<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganisationInvitationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'uuid' => $this->uuid,

            'organisation' => $this->organisation?->name,

            'email' => $this->email,

            'phone' => $this->phone,

            'role' => $this->role,

            'expires_at' => $this->expires_at,

            'accepted_at' => $this->accepted_at,

            'created_at' => $this->created_at,

        ];
    }
}