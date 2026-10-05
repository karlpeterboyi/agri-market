<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FarmerProfile;
use Illuminate\Http\Request;

class FarmerProfileController extends Controller
{
    public function store(Request $request)
{
    $request->validate([
        'farm_name' => 'required|string|max:255',
        'region' => 'required|string|max:255',
        'district' => 'required|string|max:255',
        'gps_lat' => 'nullable|numeric',
        'gps_lng' => 'nullable|numeric',
    ]);

    $profile = FarmerProfile::updateOrCreate(
        ['user_id' => auth()->id()],
        [
            'farm_name' => $request->farm_name,
            'region' => $request->region,
            'district' => $request->district,
            'gps_lat' => $request->gps_lat,
            'gps_lng' => $request->gps_lng,
        ]
    );

    return response()->json([
        'message' => 'Profile saved successfully',
        'profile' => $profile
    ]);
}
    public function show()
    {
        return response()->json(
            auth()->user()->farmerProfile
        );
    }
}