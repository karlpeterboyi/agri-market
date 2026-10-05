<?php

namespace App\Services\Disease\Drivers;

use App\Models\DiseaseReport;

interface AiDiagnosisDriver
{
    public function diagnose(
        DiseaseReport $report
    ): array;
}