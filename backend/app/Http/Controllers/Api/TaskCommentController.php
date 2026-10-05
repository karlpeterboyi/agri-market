<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskCommentRequest;
use App\Models\Task;
use App\Models\TaskComment;

class TaskCommentController extends Controller
{
    public function store(StoreTaskCommentRequest $request, Task $task)
    {
        $comment = TaskComment::create([

            'task_id'=>$task->id,

            'user_id'=>auth()->id(),

            'comment'=>$request->comment,

        ]);

        return response()->json($comment,201);
    }
}