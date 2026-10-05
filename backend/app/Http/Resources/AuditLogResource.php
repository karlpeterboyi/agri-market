<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'uuid' => $this->uuid,

            'event' => $this->event,

            'action' => $this->action,

            'organisation' => $this->organisation?->name,

            'user' => $this->user?->name,

            'auditable_type' => class_basename($this->auditable_type),

            'auditable_id' => $this->auditable_id,

            'created_at' => $this->created_at,

        ];
    }
}