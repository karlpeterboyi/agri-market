<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiseaseDiagnosisResource extends JsonResource
{
    public function toArray(
        Request $request
    ): array {

        return [

            'id' => $this->id,

            'confidence' => $this->confidence,

            'model' => $this->model_name,

            'version' => $this->model_version,

            'verified' => $this->verified_by_expert,

            'predictions' => $this->predictions,

            'disease' => $this->disease,

        ];
    }
}