<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CropResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'name' => $this->name,

            'scientific_name' => $this->scientific_name,

            'category' => $this->category,

            'description' => $this->description,

            'image' => $this->image,

            'active' => $this->active,

            'varieties_count' => $this->whenCounted('varieties'),

            'growth_stages_count' => $this->whenCounted('growthStages'),

            'created_at' => $this->created_at,

        ];
    }
}