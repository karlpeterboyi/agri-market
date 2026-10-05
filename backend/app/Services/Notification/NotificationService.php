<?php

namespace App\Services\Notification;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    public static function send(
        User $user,
        string $type,
        string $title,
        string $message,
        array $data = []
    ): Notification {

        return Notification::create([

            'organisation_id' => null,

            'user_id' => $user->id,

            'type' => $type,

            'title' => $title,

            'message' => $message,

            'data' => $data,

            'channel' => 'in_app',

        ]);

    }
}