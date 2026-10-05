<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskChecklistRequest;
use App\Models\Task;
use App\Models\TaskChecklist;

class TaskChecklistController extends Controller
{
    public function store(StoreTaskChecklistRequest $request, Task $task)
    {
        $item = TaskChecklist::create([

            'task_id'=>$task->id,

            'item'=>$request->item,

        ]);

        return response()->json($item,201);
    }

    public function complete(TaskChecklist $taskChecklist)
    {
        $taskChecklist->update([

            'completed'=>true,

            'completed_at'=>now(),

        ]);

        return response()->json([
            'message'=>'Checklist item completed.',
        ]);
    }
}