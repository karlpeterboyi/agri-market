<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiseaseVerificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'disease_diagnosis_id' => $this->disease_diagnosis,
            'extension_officer_id' => $this->extension_officer_id,
            'confirmed' => (bool) $this->confirmed,
            'notes' => $this->notes,

            // Conditional Relationships
            'disease_diagnosis' => new DiseaseDiagnosisResource($this->whenLoaded('diseaseDiagnosis')),
            'extension_officer' => new ExtensionOfficerResource($this->whenLoaded('extensionOfficer')),

            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
