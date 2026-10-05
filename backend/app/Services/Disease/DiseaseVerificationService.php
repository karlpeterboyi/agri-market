<?php

namespace App\Services\Disease;

use App\Models\DiseaseDiagnosis;
use App\Models\DiseaseReport;
use App\Models\DiseaseVerification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DiseaseVerificationService
{
    /**
     * Create a new verification for a disease diagnosis and handle side effects.
     *
     * @param  array<string, mixed>  $data
     * @return DiseaseVerification
     */
    public function verifyDiagnosis(array $data): DiseaseVerification
    {
        return DB::transaction(function () use ($data) {
            $diagnosis = DiseaseDiagnosis::with('diseaseReport')->findOrFail($data['disease_diagnosis_id']);

            // Prevent duplicate verifications by the same extension officer
            $existingVerification = DiseaseVerification::where('disease_diagnosis', $diagnosis->id)
                ->where('extension_officer_id', $data['extension_officer_id'])
                ->first();

            if ($existingVerification) {
                throw ValidationException::withMessages([
                    'extension_officer_id' => ['You have already submitted a verification for this diagnosis.'],
                ]);
            }

            // 1. Create the verification record
            $verification = DiseaseVerification::create([
                'disease_diagnosis' => $diagnosis->id,
                'extension_officer_id' => $data['extension_officer_id'],
                'confirmed' => $data['confirmed'],
                'notes' => $data['notes'] ?? null,
            ]);

            // 2. Update the parent report status based on verification outcome
            $report = $diagnosis->diseaseReport;

            if ($report) {
                if ($data['confirmed']) {
                    $report->update([
                        'status' => 'confirmed',
                    ]);

                    // Check if an outbreak threshold has been reached for this area/commodity
                    $this->checkForOutbreakCondition($report);
                } else {
                    // Check if there are other confirmed diagnoses before setting status
                    $hasConfirmedOther = DiseaseVerification::whereIn('disease_diagnosis', $report->diagnoses()->pluck('id'))
                        ->where('confirmed', true)
                        ->exists();

                    if (!$hasConfirmedOther) {
                        $report->update([
                            'status' => 'pending',
                        ]);
                    }
                }
            }

            // TODO: Notify farmer

            // TODO: Update disease analytics

            // TODO: Update outbreak statistics

            // TODO: Recommend marketplace products

            // TODO: Notify finance module if reportable disease

            // TODO: Notify nearby farmers if outbreak threshold reached

            return $verification->load(['diseaseDiagnosis', 'extensionOfficer']);
        });
    }

    /**
     * Evaluate if a verified report warrants triggering an outbreak alert or record.
     */
    protected function checkForOutbreakCondition(DiseaseReport $report): void
    {
        // Example logic: Count verified reports for the same disease/commodity in the district within 14 days
        $recentVerifiedCount = DiseaseReport::where('district', $report->district)
            ->where('commodity_type', $report->commodity_type)
            ->where('status', 'confirmed')
            ->where('created_at', '>=', now()->subDays(14))
            ->count();

        // If threshold (e.g., 5 confirmed cases in 14 days) is met, handle outbreak logic
        if ($recentVerifiedCount >= 5) {
            // Outbreak condition logic here
        }
    }
}
