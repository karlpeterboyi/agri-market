<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskAssignment;
use App\Models\TaskChecklist;
use App\Models\TaskComment;
use App\Models\WorkflowReminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    /**
     * Display all tasks.
     */
    public function index(Request $request)
    {
        $query = Task::with([
            'organisation',
            'farm',
            'creator',
            'assignments.user',
            'comments.user',
            'checklist',
            'reminders',
            'workflowInstance',
        ]);

        if ($request->filled('organisation_id')) {
            $query->where(
                'organisation_id',
                $request->organisation_id
            );
        }

        if ($request->filled('farm_id')) {
            $query->where(
                'farm_id',
                $request->farm_id
            );
        }

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->status
            );
        }

        if ($request->filled('priority')) {
            $query->where(
                'priority',
                $request->priority
            );
        }

        return response()->json([
            'success' => true,
            'data' => $query
                ->latest()
                ->paginate(20),
        ]);
    }

    /**
     * Store a newly created task.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'organisation_id' => [
                'required',
                'exists:organisations,id',
            ],
            'farm_id' => [
                'nullable',
                'exists:farms,id',
            ],
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'priority' => [
                'nullable',
                'in:low,medium,high,critical',
            ],
            'status' => [
                'nullable',
                'in:pending,in_progress,completed,cancelled',
            ],
            'start_date' => [
                'nullable',
                'date',
            ],
            'due_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],
        ]);

        DB::beginTransaction();

        try {

            $task = Task::create([
                'organisation_id' => $validated['organisation_id'],
                'farm_id' => $validated['farm_id'] ?? null,
                'created_by' => auth()->id(),
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'priority' => $validated['priority'] ?? 'medium',
                'status' => $validated['status'] ?? 'pending',
                'start_date' => $validated['start_date'] ?? null,
                'due_date' => $validated['due_date'] ?? null,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Task created successfully.',
                'data' => $task->load([
                    'organisation',
                    'farm',
                    'creator',
                ]),
            ], 201);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Task creation failed.',
                'error' => $e->getMessage(),
            ], 500);

        }
    }

    /**
     * Display the specified task.
     */
    public function show(Task $task)
    {
        return response()->json([
            'success' => true,
            'data' => $task->load([
                'organisation',
                'farm',
                'creator',
                'assignments.user',
                'comments.user',
                'checklist',
                'reminders',
                'workflowInstance',
            ]),
        ]);
    }

    /**
     * Update the specified task.
     */
    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'organisation_id' => 'sometimes|exists:organisations,id',
            'farm_id' => 'nullable|exists:farms,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'sometimes|in:low,medium,high,critical',
            'status' => 'sometimes|in:pending,in_progress,completed,cancelled',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $task->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully.',
            'data' => $task->fresh()->load([
                'organisation',
                'farm',
                'creator',
            ]),
        ]);
    }

    /**
     * Remove the specified task.
     */
    public function destroy(Task $task)
    {
        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully.',
        ]);
    }

    /**
     * Start a task.
     */
    public function startTask(Task $task)
    {
        if ($task->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending tasks can be started.',
            ], 422);
        }

        $task->update([
            'status' => 'in_progress',
            'start_date' => now()->toDateString(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Task started successfully.',
            'data' => $task,
        ]);
    }

    /**
     * Complete a task.
     */
    public function completeTask(Task $task)
    {
        if ($task->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Task is already completed.',
            ], 422);
        }

        $task->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        TaskAssignment::where('task_id', $task->id)
            ->update([
                'completed_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Task completed successfully.',
            'data' => $task->fresh(),
        ]);
    }

    /**
     * Cancel a task.
     */
    public function cancelTask(Task $task)
    {
        $task->update([
            'status' => 'cancelled',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Task cancelled successfully.',
            'data' => $task,
        ]);
    }

    /**
     * Reopen a task.
     */
    public function reopenTask(Task $task)
    {
        $task->update([
            'status' => 'pending',
            'completed_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Task reopened successfully.',
            'data' => $task,
        ]);
    }

    /**
     * Assign a user to a task.
     */
    public function assignUser(Request $request, Task $task)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $assignment = TaskAssignment::firstOrCreate(
            [
                'task_id' => $task->id,
                'user_id' => $validated['user_id'],
            ],
            [
                'accepted_at' => null,
                'completed_at' => null,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'User assigned successfully.',
            'data' => $assignment->load('user'),
        ], 201);
    }

    /**
     * Remove a user assignment.
     */
    public function removeAssignment(Task $task, $userId)
    {
        $assignment = TaskAssignment::where('task_id', $task->id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $assignment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Assignment removed successfully.',
        ]);
    }

    /**
     * Accept a task assignment.
     */
    public function acceptAssignment(Task $task)
    {
        $assignment = TaskAssignment::where('task_id', $task->id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($assignment->accepted_at) {
            return response()->json([
                'success' => false,
                'message' => 'Assignment already accepted.',
            ], 422);
        }

        $assignment->update([
            'accepted_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Assignment accepted.',
            'data' => $assignment,
        ]);
    }

    /**
     * Get tasks assigned to the authenticated user.
     */
    public function myTasks(Request $request)
    {
        $query = Task::whereHas('assignments', function ($q) {
            $q->where('user_id', auth()->id());
        });

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json([
            'success' => true,
            'data' => $query
                ->with([
                    'organisation',
                    'farm',
                    'creator',
                    'assignments.user',
                ])
                ->latest()
                ->paginate(20),
        ]);
    }

    /**
     * View users assigned to a task.
     */
    public function assignedUsers(Task $task)
    {
        return response()->json([
            'success' => true,
            'data' => $task->assignments()
                ->with('user')
                ->get(),
        ]);
    }

    /**
     * Get overdue tasks.
     */
    public function overdue()
    {
        return response()->json([
            'success' => true,
            'data' => Task::with([
                    'organisation',
                    'farm',
                    'creator',
                ])
                ->whereDate('due_date', '<', today())
                ->whereNotIn('status', [
                    'completed',
                    'cancelled',
                ])
                ->orderBy('due_date')
                ->get(),
        ]);
    }

    /**
     * Get tasks due today.
     */
    public function dueToday()
    {
        return response()->json([
            'success' => true,
            'data' => Task::with([
                    'organisation',
                    'farm',
                    'creator',
                ])
                ->whereDate('due_date', today())
                ->orderBy('priority')
                ->get(),
        ]);
    }

    /**
     * Get upcoming tasks.
     */
    public function upcoming()
    {
        return response()->json([
            'success' => true,
            'data' => Task::with([
                    'organisation',
                    'farm',
                    'creator',
                ])
                ->whereDate('due_date', '>', today())
                ->where('status', '!=', 'completed')
                ->orderBy('due_date')
                ->get(),
        ]);
    }

    /**
     * Add a reminder to a task.
     */
    public function addReminder(Request $request, Task $task)
    {
        $validated = $request->validate([
            'days_before' => 'required|integer|min:0',
        ]);

        $reminder = WorkflowReminder::create([
            'task_id' => $task->id,
            'days_before' => $validated['days_before'],
            'sent' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reminder added successfully.',
            'data' => $reminder,
        ], 201);
    }

    /**
     * Delete a reminder.
     */
    public function removeReminder(WorkflowReminder $workflowReminder)
    {
        $workflowReminder->delete();

        return response()->json([
            'success' => true,
            'message' => 'Reminder deleted successfully.',
        ]);
    }

    /**
     * Task progress summary.
     */
    public function progress(Task $task)
    {
        $totalChecklist = $task->checklist()->count();

        $completedChecklist = $task->checklist()
            ->where('completed', true)
            ->count();

        $percentage = $totalChecklist > 0
            ? round(($completedChecklist / $totalChecklist) * 100, 2)
            : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'task_id' => $task->id,
                'status' => $task->status,
                'total_checklist_items' => $totalChecklist,
                'completed_checklist_items' => $completedChecklist,
                'progress_percentage' => $percentage,
            ],
        ]);
    }

    /**
     * Task statistics.
     */
    public function statistics()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'total_tasks' => Task::count(),
                'pending_tasks' => Task::where('status', 'pending')->count(),
                'in_progress_tasks' => Task::where('status', 'in_progress')->count(),
                'completed_tasks' => Task::where('status', 'completed')->count(),
                'cancelled_tasks' => Task::where('status', 'cancelled')->count(),

                'low_priority' => Task::where('priority', 'low')->count(),
                'medium_priority' => Task::where('priority', 'medium')->count(),
                'high_priority' => Task::where('priority', 'high')->count(),
                'critical_priority' => Task::where('priority', 'critical')->count(),

                'overdue_tasks' => Task::whereDate('due_date', '<', today())
                    ->whereNotIn('status', ['completed', 'cancelled'])
                    ->count(),

                'due_today' => Task::whereDate('due_date', today())->count(),
            ],
        ]);
    }

    /**
     * Dashboard summary.
     */
    public function dashboard()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'assigned_to_me' => TaskAssignment::where('user_id', auth()->id())->count(),

                'my_pending' => Task::whereHas('assignments', function ($q) {
                    $q->where('user_id', auth()->id());
                })->where('status', 'pending')->count(),

                'my_in_progress' => Task::whereHas('assignments', function ($q) {
                    $q->where('user_id', auth()->id());
                })->where('status', 'in_progress')->count(),

                'my_completed' => Task::whereHas('assignments', function ($q) {
                    $q->where('user_id', auth()->id());
                })->where('status', 'completed')->count(),

                'overdue' => Task::whereHas('assignments', function ($q) {
                    $q->where('user_id', auth()->id());
                })
                ->whereDate('due_date', '<', today())
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->count(),

                'due_today' => Task::whereHas('assignments', function ($q) {
                    $q->where('user_id', auth()->id());
                })
                ->whereDate('due_date', today())
                ->count(),
            ],
        ]);
    }
}
