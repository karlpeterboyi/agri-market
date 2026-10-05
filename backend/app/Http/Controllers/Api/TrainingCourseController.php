<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CourseEnrollment;
use App\Models\TrainingCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TrainingCourseController extends Controller
{
    /**
     * Public listing of published courses.
     */
    public function index(Request $request)
    {
        $query = TrainingCourse::query()
            ->where('is_published', true)
            ->with('institution:id,name')
            ->withCount('enrollments');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        if ($request->filled('language')) {
            $query->where('language', $request->language);
        }

        if ($request->boolean('featured')) {
            $query->where('featured', true);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%");
            });
        }

        return response()->json(
            $query->orderByDesc('featured')->latest()->paginate(12)
        );
    }

    public function show($idOrSlug)
    {
        $course = TrainingCourse::with(['institution', 'creator:id,name'])
            ->where(function ($q) use ($idOrSlug) {
                $q->where('id', $idOrSlug)->orWhere('slug', $idOrSlug);
            })
            ->firstOrFail();

        // Public viewers only see published; owner/admin can preview drafts
        $user = auth()->user();
        $isOwner = $user && (int) $course->created_by === (int) $user->id;
        $isAdmin = $user && ($user->role ?? '') === 'admin';
        if (!$course->is_published && !$isOwner && !$isAdmin) {
            abort(404, 'Course not found or not published.');
        }

        $enrollment = null;
        if (auth()->check()) {
            $enrollment = CourseEnrollment::where('training_course_id', $course->id)
                ->where('user_id', auth()->id())
                ->first();
        }

        $access = false;
        if ($enrollment) {
            $pay = $enrollment->payment_status ?? 'not_required';
            $access = in_array($enrollment->status, ['enrolled', 'in_progress', 'completed'], true)
                && in_array($pay, ['not_required', 'paid'], true);
        }

        return response()->json([
            'course' => $course,
            'enrollment' => $enrollment,
            'access_granted' => $access,
        ]);
    }

    /**
     * Admin / Institution create course
     */
    public function store(Request $request)
    {
        $this->authorizeCreator();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'level' => 'nullable|in:beginner,intermediate,advanced',
            'language' => 'nullable|string|max:10',
            'format' => 'nullable|string|max:50',
            'duration_minutes' => 'nullable|integer|min:1',
            'thumbnail_path' => 'nullable|string',
            'video_url' => 'nullable|string|max:500',
            'content' => 'nullable|string',
            'learning_outcomes' => 'nullable|array',
            'modules' => 'nullable|array',
            'is_free' => 'nullable|boolean',
            'price' => 'nullable|numeric|min:0',
            'is_published' => 'nullable|boolean',
            'featured' => 'nullable|boolean',
            'certificate_enabled' => 'nullable|boolean',
            'research_institution_id' => 'nullable|exists:research_institutions,id',
        ]);

        if (empty($validated['research_institution_id'])) {
            $inst = \App\Models\ResearchInstitution::where('owner_user_id', auth()->id())->first();
            if ($inst) {
                $validated['research_institution_id'] = $inst->id;
            }
        }

        $payload = [
            ...$validated,
            'created_by' => auth()->id(),
            'is_free' => array_key_exists('is_free', $validated) ? (bool) $validated['is_free'] : true,
            'is_published' => array_key_exists('is_published', $validated) ? (bool) $validated['is_published'] : false,
            'featured' => array_key_exists('featured', $validated) ? (bool) $validated['featured'] : false,
            'level' => $validated['level'] ?? 'beginner',
            'language' => $validated['language'] ?? 'sw',
            'format' => $validated['format'] ?? 'self_paced',
            'description' => $validated['description'] ?? $validated['title'],
            'price' => !empty($validated['is_free']) ? 0 : ($validated['price'] ?? 0),
        ];

        // Drop columns that may not exist yet if migration not run
        try {
            $course = TrainingCourse::create($payload);
        } catch (\Throwable $e) {
            unset($payload['format'], $payload['certificate_enabled']);
            $course = TrainingCourse::create($payload);
        }

        return response()->json([
            'message' => 'Course saved',
            'data' => $course,
        ], 201);
    }

    public function update(Request $request, TrainingCourse $trainingCourse)
    {
        $this->authorizeCreator($trainingCourse);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'level' => 'nullable|in:beginner,intermediate,advanced',
            'language' => 'nullable|in:sw,en',
            'duration_minutes' => 'nullable|integer|min:1',
            'thumbnail_path' => 'nullable|string',
            'video_url' => 'nullable|url',
            'content' => 'nullable|string',
            'learning_outcomes' => 'nullable|array',
            'modules' => 'nullable|array',
            'is_free' => 'boolean',
            'price' => 'nullable|numeric|min:0',
            'is_published' => 'boolean',
            'featured' => 'boolean',
        ]);

        $trainingCourse->update($validated);

        return response()->json($trainingCourse->fresh());
    }

    public function destroy(TrainingCourse $trainingCourse)
    {
        $this->authorizeCreator($trainingCourse);
        $trainingCourse->delete();
        return response()->json(['message' => 'Course deleted.']);
    }

    /**
     * Enroll the current user
     */
    public function enroll(TrainingCourse $trainingCourse)
    {
        if (!$trainingCourse->is_published) {
            return response()->json(['message' => 'Course is not published.'], 422);
        }

        $user = auth()->user();
        $isFree = (bool) $trainingCourse->is_free || (float) ($trainingCourse->price ?? 0) <= 0;

        $enrollment = CourseEnrollment::firstOrCreate(
            [
                'training_course_id' => $trainingCourse->id,
                'user_id' => $user->id,
            ],
            [
                'status' => $isFree ? 'enrolled' : 'pending_payment',
                'payment_status' => $isFree ? 'not_required' : 'pending',
                'progress_percent' => 0,
                'enrolled_at' => now(),
            ]
        );

        // Already paid / active
        if (in_array($enrollment->status, ['enrolled', 'in_progress', 'completed'], true)
            && in_array($enrollment->payment_status ?? 'not_required', ['not_required', 'paid'], true)) {
            return response()->json([
                'message' => 'Already enrolled.',
                'enrollment' => $enrollment,
                'access_granted' => true,
                'requires_payment' => false,
            ]);
        }

        if ($isFree) {
            if ($enrollment->wasRecentlyCreated) {
                $trainingCourse->increment('enrollments_count');
            }
            $enrollment->update([
                'status' => 'enrolled',
                'payment_status' => 'not_required',
            ]);

            return response()->json([
                'message' => 'Successfully enrolled.',
                'enrollment' => $enrollment->fresh(),
                'access_granted' => true,
                'requires_payment' => false,
            ], 201);
        }

        // Paid course → pending until Pesapal confirms
        $enrollment->update([
            'status' => 'pending_payment',
            'payment_status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Payment required to access this course.',
            'enrollment' => $enrollment->fresh(),
            'access_granted' => false,
            'requires_payment' => true,
            'amount' => (float) $trainingCourse->price,
            'currency' => 'TZS',
            'checkout_path' => '/knowledge/courses/'.$trainingCourse->id.'/pay',
        ], 202);
    }

    /**
     * Start Pesapal payment for a paid course enrollment.
     */
    public function initiatePayment(Request $request, TrainingCourse $trainingCourse, \App\Services\PesapalService $pesapal)
    {
        if (!$trainingCourse->is_published) {
            return response()->json(['message' => 'Course is not published.'], 422);
        }

        $isFree = (bool) $trainingCourse->is_free || (float) ($trainingCourse->price ?? 0) <= 0;
        if ($isFree) {
            return response()->json(['message' => 'This course is free — enroll without payment.'], 422);
        }

        $user = auth()->user();
        $enrollment = CourseEnrollment::firstOrCreate(
            [
                'training_course_id' => $trainingCourse->id,
                'user_id' => $user->id,
            ],
            [
                'status' => 'pending_payment',
                'payment_status' => 'pending',
                'progress_percent' => 0,
                'enrolled_at' => now(),
            ]
        );

        if (($enrollment->payment_status ?? '') === 'paid'
            && in_array($enrollment->status, ['enrolled', 'in_progress', 'completed'], true)) {
            return response()->json([
                'message' => 'Already paid.',
                'access_granted' => true,
                'enrollment' => $enrollment,
            ]);
        }

        $data = $request->validate([
            'phone' => 'required|string|max:30',
        ]);

        $payment = \App\Models\Payment::create([
            'order_id' => null,
            'payer_id' => $user->id,
            'amount' => $trainingCourse->price,
            'method' => 'pesapal',
            'phone' => $data['phone'],
            'status' => 'pending',
            'payment_type' => 'course',
            'course_enrollment_id' => $enrollment->id,
        ]);

        $names = explode(' ', (string) $user->name, 2);
        $response = $pesapal->checkoutPayment(
            $payment,
            'Course: '.$trainingCourse->title,
            $user->email ?? 'student@mkulimahub.local',
            $data['phone'],
            $names[0] ?: 'Student',
            $names[1] ?? ''
        );

        return response()->json([
            'message' => 'Redirect to Pesapal',
            'payment' => $payment->fresh(),
            'enrollment' => $enrollment->fresh(),
            'redirect_url' => $response['redirect_url'] ?? null,
            'mock' => $response['mock'] ?? false,
        ]);
    }

    /**
     * Update progress
     */
    public function updateProgress(Request $request, TrainingCourse $trainingCourse)
    {
        $enrollment = CourseEnrollment::where('training_course_id', $trainingCourse->id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if (($enrollment->payment_status ?? 'not_required') === 'pending'
            || ($enrollment->status ?? '') === 'pending_payment') {
            return response()->json([
                'message' => 'Payment required before accessing course materials.',
                'code' => 'payment_required',
                'checkout_path' => '/knowledge/courses/'.$trainingCourse->id.'/pay',
            ], 402);
        }

        $validated = $request->validate([
            'progress_percent' => 'required|integer|min:0|max:100',
        ]);

        $enrollment->progress_percent = $validated['progress_percent'];
        $enrollment->status = $validated['progress_percent'] >= 100 ? 'completed' : 'in_progress';

        if ($validated['progress_percent'] >= 100 && !$enrollment->completed_at) {
            $enrollment->markCompleted();
        } else {
            $enrollment->save();
        }

        return response()->json($enrollment->fresh());
    }

    /**
     * My enrollments
     */
    public function myEnrollments()
    {
        $enrollments = CourseEnrollment::with('course')
            ->where('user_id', auth()->id())
            ->latest('enrolled_at')
            ->get();

        return response()->json($enrollments);
    }

    protected function authorizeCreator(?TrainingCourse $course = null): void
    {
        $user = auth()->user();
        $role = $user->role ?? '';
        $allowed = in_array($role, ['admin', 'educator', 'provider', 'researcher', 'extension_officer'], true);
        if (!$allowed) {
            abort(403, 'Only educators, providers, or admin can manage courses. Your role: '.$role);
        }
        if ($course && $course->created_by !== $user->id && $role !== 'admin') {
            abort(403, 'You can only manage your own courses.');
        }
    }
}
