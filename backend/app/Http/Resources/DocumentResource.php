<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'uuid' => $this->uuid,

            'name' => $this->original_name,

            'category' => $this->category,

            'mime_type' => $this->mime_type,

            'size' => $this->size,

            'visibility' => $this->visibility,

            'uploaded_by' => $this->uploader?->name,

            'created_at' => $this->created_at,

        ];
    }
}