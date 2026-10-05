<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FarmerAdvisory;

class FarmerAdvisoryController extends Controller
{
    public function index()
    {
        return FarmerAdvisory::query()

            ->where('user_id', auth()->id())

            ->latest('advisory_date')

            ->paginate();
    }

    public function show(FarmerAdvisory $farmerAdvisory)
    {
        abort_unless(

            $farmerAdvisory->user_id == auth()->id(),

            403

        );

        $farmerAdvisory->update([

            'read' => true,

        ]);

        return response()->json(

            $farmerAdvisory

        );
    }
}