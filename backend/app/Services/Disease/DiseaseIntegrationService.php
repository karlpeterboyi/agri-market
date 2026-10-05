<?php

namespace App\Services\Disease;

use App\Models\DiseaseReport;
use App\Models\ProductListing;
use App\Models\ExtensionOfficer;
use App\Models\ResearchPublication;
use App\Models\LoanApplication;
use App\Models\User;

class DiseaseIntegrationService
{
    /**
     * Get recommended marketplace products.
     */
    public function recommendedProducts(DiseaseReport $report)
    {
        if (!$report->disease) {
            return collect();
        }

        return ProductListing::query()
            ->where(function ($query) use ($report) {
                $query->where('title', 'ILIKE', '%' . $report->commodity_name . '%')
                      ->orWhere('description', 'ILIKE', '%' . $report->commodity_name . '%');
            })
            ->where('status', 'active')
            ->limit(10)
            ->get();
    }

    /**
     * Find nearby extension officers.
     */
    public function nearbyExtensionOfficers(DiseaseReport $report)
    {
        return ExtensionOfficer::query()
            ->where('available', true)
            ->where('verified', true)
            ->where('region', $report->region)
            ->orderByDesc('rating')
            ->limit(5)
            ->get();
    }

    /**
     * Related research publications.
     */
    public function relatedResearch(DiseaseReport $report)
    {
        return ResearchPublication::query()
            ->where(function ($query) use ($report) {

                $query->where('crop', $report->commodity_name)
                      ->orWhere('livestock', $report->commodity_name);

            })
            ->latest()
            ->limit(5)
            ->get();
    }

    /**
     * Farmer has active loans?
     */
    public function activeLoans(DiseaseReport $report)
    {
        return LoanApplication::query()

            ->where('user_id', $report->user_id)

            ->whereIn('status', [

                'approved',

                'disbursed',

                'active',

            ])

            ->get();
    }

    /**
     * Notify lender if required.
     */
    public function notifyFinancialInstitution(DiseaseReport $report)
    {
        $loans = $this->activeLoans($report);

        foreach ($loans as $loan) {

            // Event will be implemented later

            event(new \App\Events\DiseaseReportedOnFinancedFarm(

                $loan,

                $report

            ));

        }
    }
}