<?php

namespace App\Services\Disease\Drivers;

use App\Models\Disease;

use App\Models\DiseaseReport;

class DummyDiagnosisDriver implements AiDiagnosisDriver
{
    public function diagnose(
        DiseaseReport $report
    ): array {

        $disease = Disease::first();

        return [

            'disease_id' => $disease?->id,

            'confidence' => 92.50,

            'model_name' => 'Dummy AI',

            'model_version' => '1.0',

            'predictions' => [

                [
                    'name' => $disease?->name,

                    'confidence' => 92.50,
                ],

            ],

        ];
    }
}