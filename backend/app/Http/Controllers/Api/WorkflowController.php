<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organisation;
use App\Models\Workflow;
use App\Models\WorkflowInstance;
use App\Models\WorkflowSchedule;
use App\Models\WorkflowStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkflowController extends Controller
{
    /**
     * Display a listing of workflows.
     */
    public function index(Request $request)
    {
        $query = Workflow::with([
            'organisation',
            'steps',
            'schedules',
        ]);

        if ($request->filled('organisation_id')) {
            $query->where(
                'organisation_id',
                $request->organisation_id
            );
        }

        if ($request->filled('module')) {
            $query->where(
                'module',
                $request->module
            );
        }

        if ($request->filled('active')) {
            $query->where(
                'active',
                filter_var(
                    $request->active,
                    FILTER_VALIDATE_BOOLEAN
                )
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
     * Store a newly created workflow.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'organisation_id' => [
                'required',
                'exists:organisations,id',
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'module' => [
                'required',
                'string',
                'max:100',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'active' => [
                'nullable',
                'boolean',
            ],

            // Optional initial workflow steps
            'steps' => [
                'nullable',
                'array',
            ],
            'steps.*.name' => [
                'required_with:steps',
                'string',
                'max:255',
            ],
            'steps.*.step_order' => [
                'required_with:steps',
                'integer',
                'min:1',
            ],
            'steps.*.task_title' => [
                'required_with:steps',
                'string',
                'max:255',
            ],
            'steps.*.task_description' => [
                'nullable',
                'string',
            ],
            'steps.*.assign_role' => [
                'nullable',
                'string',
                'max:100',
            ],
            'steps.*.due_after_days' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'steps.*.approval_required' => [
                'nullable',
                'boolean',
            ],

            // Optional schedule
            'schedule' => [
                'nullable',
                'array',
            ],
            'schedule.frequency' => [
                'required_with:schedule',
                'in:once,daily,weekly,monthly,seasonal,yearly',
            ],
            'schedule.start_date' => [
                'required_with:schedule',
                'date',
            ],
            'schedule.end_date' => [
                'nullable',
                'date',
                'after_or_equal:schedule.start_date',
            ],
            'schedule.next_run_at' => [
                'required_with:schedule',
                'date',
            ],
            'schedule.active' => [
                'nullable',
                'boolean',
            ],
        ]);

        DB::beginTransaction();

        try {

            $organisation = Organisation::findOrFail(
                $validated['organisation_id']
            );

            $workflow = Workflow::create([
                'organisation_id' => $organisation->id,
                'name' => $validated['name'],
                'module' => $validated['module'],
                'description' => $validated['description'] ?? null,
                'active' => $validated['active'] ?? true,
            ]);

            /**
             * Create workflow steps
             */
            if (!empty($validated['steps'])) {

                foreach ($validated['steps'] as $step) {

                    $workflow->steps()->create([
                        'name' => $step['name'],
                        'step_order' => $step['step_order'],
                        'task_title' => $step['task_title'],
                        'task_description' =>
                            $step['task_description'] ?? null,
                        'assign_role' =>
                            $step['assign_role'] ?? null,
                        'due_after_days' =>
                            $step['due_after_days'] ?? 0,
                        'approval_required' =>
                            $step['approval_required'] ?? false,
                    ]);

                }

            }

            /**
             * Create workflow schedule
             */
            if (!empty($validated['schedule'])) {

                $workflow->schedules()->create([
                    'frequency' =>
                        $validated['schedule']['frequency'],
                    'start_date' =>
                        $validated['schedule']['start_date'],
                    'end_date' =>
                        $validated['schedule']['end_date'] ?? null,
                    'next_run_at' =>
                        $validated['schedule']['next_run_at'],
                    'active' =>
                        $validated['schedule']['active'] ?? true,
                ]);

            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Workflow created successfully.',
                'data' => $workflow->load([
                    'organisation',
                    'steps',
                    'schedules',
                ]),
            ], 201);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create workflow.',
                'error' => $e->getMessage(),
            ], 500);

        }
    }

    /**
     * Display the specified workflow.
     */
    public function show(Workflow $workflow)
    {
        $workflow->load([
            'organisation',
            'steps',
            'schedules',
            'instances',
        ]);

        return response()->json([
            'success' => true,
            'data' => $workflow,
        ]);
    }

    /**
     * Update the specified workflow.
     */
    public function update(Request $request, Workflow $workflow)
    {
        $validated = $request->validate([
            'organisation_id' => [
                'sometimes',
                'exists:organisations,id',
            ],
            'name' => [
                'sometimes',
                'string',
                'max:255',
            ],
            'module' => [
                'sometimes',
                'string',
                'max:100',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'active' => [
                'sometimes',
                'boolean',
            ],
        ]);

        DB::beginTransaction();

        try {

            $workflow->update($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Workflow updated successfully.',
                'data' => $workflow->load([
                    'organisation',
                    'steps',
                    'schedules',
                    'instances',
                ]),
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update workflow.',
                'error' => $e->getMessage(),
            ], 500);

        }
    }

    /**
     * Remove the specified workflow.
     */
    public function destroy(Workflow $workflow)
    {
        DB::beginTransaction();

        try {

            $workflow->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Workflow deleted successfully.',
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete workflow.',
                'error' => $e->getMessage(),
            ], 500);

        }
    }

    /**
     * Activate workflow.
     */
    public function activate(Workflow $workflow)
    {
        $workflow->update([
            'active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Workflow activated successfully.',
            'data' => $workflow,
        ]);
    }

    /**
     * Deactivate workflow.
     */
    public function deactivate(Workflow $workflow)
    {
        $workflow->update([
            'active' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Workflow deactivated successfully.',
            'data' => $workflow,
        ]);
    }

    /**
     * Start a new workflow instance.
     */
    public function startInstance(Request $request, Workflow $workflow)
    {
        $validated = $request->validate([
            'workflowable_type' => [
                'required',
                'string',
            ],
            'workflowable_id' => [
                'required',
                'integer',
            ],
        ]);

        DB::beginTransaction();

        try {

            $instance = $workflow->instances()->create([
                'workflowable_type' => $validated['workflowable_type'],
                'workflowable_id'   => $validated['workflowable_id'],
                'status'            => 'running',
                'current_step'      => 1,
                'started_at'        => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Workflow started successfully.',
                'data' => $instance->load('workflow'),
            ], 201);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Unable to start workflow.',
                'error' => $e->getMessage(),
            ], 500);

        }
    }

    /**
     * Advance workflow to the next step.
     */
    public function advanceToNextStep($instanceId)
    {
        $instance = WorkflowInstance::with([
            'workflow.steps'
        ])->findOrFail($instanceId);

        if ($instance->status !== 'running') {

            return response()->json([
                'success' => false,
                'message' => 'Workflow is not running.',
            ], 422);

        }

        $totalSteps = $instance->workflow
            ->steps
            ->count();

        if ($instance->current_step >= $totalSteps) {

            $instance->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Workflow completed successfully.',
                'data' => $instance,
            ]);

        }

        $instance->increment('current_step');

        return response()->json([
            'success' => true,
            'message' => 'Workflow advanced successfully.',
            'data' => $instance->fresh(),
        ]);
    }

    /**
     * Complete workflow instance.
     */
    public function completeInstance($instanceId)
    {
        $instance = WorkflowInstance::findOrFail(
            $instanceId
        );

        $instance->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Workflow completed.',
            'data' => $instance,
        ]);
    }

    /**
     * Cancel workflow instance.
     */
    public function cancelInstance($instanceId)
    {
        $instance = WorkflowInstance::findOrFail(
            $instanceId
        );

        $instance->update([
            'status' => 'cancelled',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Workflow cancelled.',
            'data' => $instance,
        ]);
    }

    /**
     * Restart workflow instance.
     */
    public function restartInstance($instanceId)
    {
        $instance = WorkflowInstance::findOrFail(
            $instanceId
        );

        $instance->update([
            'status' => 'running',
            'current_step' => 1,
            'started_at' => now(),
            'completed_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Workflow restarted successfully.',
            'data' => $instance,
        ]);
    }

    /**
     * Add a step to a workflow.
     */
    public function storeStep(Request $request, Workflow $workflow)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'step_order' => 'required|integer|min:1',
            'task_title' => 'required|string|max:255',
            'task_description' => 'nullable|string',
            'assign_role' => 'nullable|string|max:100',
            'due_after_days' => 'nullable|integer|min:0',
            'approval_required' => 'nullable|boolean',
        ]);

        $step = $workflow->steps()->create([
            'name' => $validated['name'],
            'step_order' => $validated['step_order'],
            'task_title' => $validated['task_title'],
            'task_description' => $validated['task_description'] ?? null,
            'assign_role' => $validated['assign_role'] ?? null,
            'due_after_days' => $validated['due_after_days'] ?? 0,
            'approval_required' => $validated['approval_required'] ?? false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Workflow step created successfully.',
            'data' => $step,
        ], 201);
    }

    /**
     * Update a workflow step.
     */
    public function updateStep(Request $request, $stepId)
    {
        $step = WorkflowStep::findOrFail($stepId);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'step_order' => 'sometimes|integer|min:1',
            'task_title' => 'sometimes|string|max:255',
            'task_description' => 'nullable|string',
            'assign_role' => 'nullable|string|max:100',
            'due_after_days' => 'sometimes|integer|min:0',
            'approval_required' => 'sometimes|boolean',
        ]);

        $step->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Workflow step updated successfully.',
            'data' => $step,
        ]);
    }

    /**
     * Delete a workflow step.
     */
    public function destroyStep($stepId)
    {
        $step = WorkflowStep::findOrFail($stepId);

        $step->delete();

        return response()->json([
            'success' => true,
            'message' => 'Workflow step deleted successfully.',
        ]);
    }

    /**
     * Create a workflow schedule.
     */
    public function storeSchedule(Request $request, Workflow $workflow)
    {
        $validated = $request->validate([
            'frequency' => 'required|in:once,daily,weekly,monthly,seasonal,yearly',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'next_run_at' => 'required|date',
            'active' => 'nullable|boolean',
        ]);

        $schedule = $workflow->schedules()->create([
            'frequency' => $validated['frequency'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
            'next_run_at' => $validated['next_run_at'],
            'active' => $validated['active'] ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Workflow schedule created successfully.',
            'data' => $schedule,
        ], 201);
    }

    /**
     * Update a workflow schedule.
     */
    public function updateSchedule(Request $request, $scheduleId)
    {
        $schedule = WorkflowSchedule::findOrFail($scheduleId);

        $validated = $request->validate([
            'frequency' => 'sometimes|in:once,daily,weekly,monthly,seasonal,yearly',
            'start_date' => 'sometimes|date',
            'end_date' => 'nullable|date',
            'next_run_at' => 'sometimes|date',
            'last_run_at' => 'nullable|date',
            'active' => 'sometimes|boolean',
        ]);

        $schedule->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Workflow schedule updated successfully.',
            'data' => $schedule,
        ]);
    }

    /**
     * Delete a workflow schedule.
     */
    public function destroySchedule($scheduleId)
    {
        $schedule = WorkflowSchedule::findOrFail($scheduleId);

        $schedule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Workflow schedule deleted successfully.',
        ]);
    }

    /**
     * Workflow statistics.
     */
    public function statistics()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'total_workflows' => Workflow::count(),
                'active_workflows' => Workflow::where('active', true)->count(),
                'inactive_workflows' => Workflow::where('active', false)->count(),
                'running_instances' => WorkflowInstance::where('status', 'running')->count(),
                'pending_instances' => WorkflowInstance::where('status', 'pending')->count(),
                'completed_instances' => WorkflowInstance::where('status', 'completed')->count(),
                'cancelled_instances' => WorkflowInstance::where('status', 'cancelled')->count(),
            ],
        ]);
    }
}
