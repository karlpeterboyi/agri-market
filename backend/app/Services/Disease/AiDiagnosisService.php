<?php

namespace App\Services\Disease;

use App\Models\DiseaseDiagnosis;
use App\Models\DiseaseReport;
use App\Services\Disease\Drivers\DummyDiagnosisDriver;

class AiDiagnosisService
{
    protected DummyDiagnosisDriver $driver;

    public function __construct()
    {
        $this->driver = new DummyDiagnosisDriver();
    }

    public function diagnose(
        DiseaseReport $report
    ): DiseaseDiagnosis {

        $result = $this->driver
            ->diagnose($report);

        return DiseaseDiagnosis::create([

            'disease_report_id' => $report->id,

            'disease_id' => $result['disease_id'],

            'confidence' => $result['confidence'],

            'model_name' => $result['model_name'],

            'model_version' => $result['model_version'],

            'predictions' => $result['predictions'],

        ]);
    }
}