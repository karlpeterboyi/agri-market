<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Finance\FinanceReportService;

class FinanceReportController extends Controller
{
    public function index(
        FinanceReportService $reports
    )
    {
        return response()->json([

            'dashboard' =>
                $reports->dashboard(),

            'repayment' =>
                $reports->repaymentPerformance(),

            'institutions' =>
                $reports->institutionPortfolio(),

            'crops' =>
                $reports->cropPortfolio(),

            'livestock' =>
                $reports->livestockPortfolio(),

            'regions' =>
                $reports->regionalPortfolio(),

            'gender' =>
                $reports->genderAnalysis(),

            'youth' =>
                $reports->youthPortfolio(),

        ]);
    }
}