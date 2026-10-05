<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FarmResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'owner_id' => $this->owner_id,

            'farm_code' => $this->farm_code,

            'name' => $this->name,

            'description' => $this->description,

            'farm_type' => $this->farm_type,

            'ownership_type' => $this->ownership_type,

            'country' => $this->country,

            'region' => $this->region,

            'district' => $this->district,

            'ward' => $this->ward,

            'village' => $this->village,

            'address' => $this->address,

            'latitude' => $this->latitude,

            'longitude' => $this->longitude,

            'elevation' => $this->elevation,

            'total_area_hectares' => $this->total_area_hectares,

            'cultivated_area_hectares' => $this->cultivated_area_hectares,

            'irrigated_area_hectares' => $this->irrigated_area_hectares,

            'registration_number' => $this->registration_number,

            'certification' => $this->certification,

            'status' => $this->status,

            'metadata' => $this->metadata,

            'boundary' => $this->whenLoaded('boundary'),

            'field_blocks_count' => $this->whenCounted('fieldBlocks'),

            'created_at' => $this->created_at,

            'updated_at' => $this->updated_at,

        ];
    }
}