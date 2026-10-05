<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'uuid' => $this->uuid,

            'type' => $this->type,

            'title' => $this->title,

            'message' => $this->message,

            'data' => $this->data,

            'channel' => $this->channel,

            'read_at' => $this->read_at,

            'created_at' => $this->created_at,

        ];
    }
}