<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CropCalendarResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id'=>$this->id,

            'crop'=>$this->crop,

            'variety'=>$this->variety,

            'region'=>$this->region,

            'district'=>$this->district,

            'planting_date'=>$this->planting_date,

            'expected_harvest_date'=>$this->expected_harvest_date,

            'season'=>$this->season,

            'status'=>$this->status,

            'activities'=>$this->activities,

        ];
    }
}