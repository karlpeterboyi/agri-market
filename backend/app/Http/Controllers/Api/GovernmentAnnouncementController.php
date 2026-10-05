<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GovernmentAnnouncement;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GovernmentAnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $query = GovernmentAnnouncement::published()->latest('published_at');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('region')) {
            $query->where(function ($q) use ($request) {
                $q->whereNull('region')->orWhere('region', $request->region);
            });
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('summary', 'like', "%{$s}%");
            });
        }

        return response()->json($query->paginate(15));
    }

    public function show($idOrSlug)
    {
        $item = GovernmentAnnouncement::published()
            ->where('id', $idOrSlug)
            ->orWhere('slug', $idOrSlug)
            ->firstOrFail();

        return response()->json($item);
    }

    public function store(Request $request)
    {
        $this->authorizeGovernment();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'summary' => 'nullable|string',
            'body' => 'nullable|string',
            'source_organisation' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:published_at',
            'is_published' => 'boolean',
            'attachment_path' => 'nullable|string',
        ]);

        $item = GovernmentAnnouncement::create([
            ...$validated,
            'created_by' => auth()->id(),
            'published_at' => $validated['published_at'] ?? now(),
            'is_published' => $validated['is_published'] ?? true,
            'priority' => $validated['priority'] ?? 'normal',
        ]);

        return response()->json($item, 201);
    }

    public function update(Request $request, GovernmentAnnouncement $governmentAnnouncement)
    {
        $this->authorizeGovernment();

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'category' => 'nullable|string|max:100',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'summary' => 'nullable|string',
            'body' => 'nullable|string',
            'source_organisation' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
            'is_published' => 'boolean',
            'attachment_path' => 'nullable|string',
        ]);

        $governmentAnnouncement->update($validated);

        return response()->json($governmentAnnouncement->fresh());
    }

    public function destroy(GovernmentAnnouncement $governmentAnnouncement)
    {
        $this->authorizeGovernment();
        $governmentAnnouncement->delete();
        return response()->json(['message' => 'Announcement deleted.']);
    }

    protected function authorizeGovernment(): void
    {
        if (!in_array(auth()->user()->role ?? '', ['admin', 'government_officer'])) {
            abort(403, 'Only government officers or admins can manage announcements.');
        }
    }
}
