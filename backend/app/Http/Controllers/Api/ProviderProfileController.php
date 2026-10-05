<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProviderProfile;
use Illuminate\Http\Request;

class ProviderProfileController extends Controller
{
    public function show($id)
    {
        return ProviderProfile::with('user')
            ->findOrFail($id);
    }

    public function myProfile(Request $request)
    {
        return $request->user()
            ->providerProfile;
    }

    public function update(Request $request)
    {
        $profile = $request->user()->providerProfile;

        $profile->update($request->all());

        return response()->json($profile);
    }
}