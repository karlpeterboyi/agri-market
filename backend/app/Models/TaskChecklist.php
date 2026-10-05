<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaskChecklist extends Model
{
    use HasFactory;

    protected $fillable = [

        'task_id',

        'item',

        'completed',

        'completed_at',

    ];

    protected $casts = [

        'completed'=>'boolean',

        'completed_at'=>'datetime',

    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}