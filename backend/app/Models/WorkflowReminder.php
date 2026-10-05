<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WorkflowReminder extends Model
{
    use HasFactory;

    protected $fillable = [

        'task_id',

        'days_before',

        'sent',

        'sent_at',

    ];

    protected $casts = [

        'sent' => 'boolean',

        'sent_at' => 'datetime',

    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}