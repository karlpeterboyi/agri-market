<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function index()
    {
        return NotificationResource::collection(
            Notification::where('user_id', auth()->id())
                ->latest()
                ->paginate(20)
        );
    }

    public function markAsRead(Notification $notification)
    {
        abort_unless(
            $notification->user_id === auth()->id(),
            403
        );

        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read.',
        ]);
    }
}