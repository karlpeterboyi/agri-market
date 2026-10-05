<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiseaseResource extends JsonResource
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
            'name' => $this->name,
            'code' => $this->code ?? null,
            'scientific_name' => $this->scientific_name ?? null,
            'commodity_type' => $this->commodity_type ?? null,
            'description' => $this->description ?? null,
            'symptoms' => $this->symptoms ?? null,
            'treatment' => $this->treatment ?? null,
            'prevention' => $this->prevention ?? null,
            'severity' => $this->severity ?? 'medium',
            
            // Conditional relationships when loaded
            'reports_count' => $this->whenCounted('reports'),
            'outbreaks' => DiseaseOutbreakResource::collection($this->whenLoaded('outbreaks')),
            
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
