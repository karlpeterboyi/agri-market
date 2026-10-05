<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MachineryBrand;

class MachineryBrandController extends Controller
{
    public function index()
    {
        return MachineryBrand::where('active', true)
            ->orderBy('name')
            ->get();
    }

    public function show(MachineryBrand $machineryBrand)
    {
        return $machineryBrand;
    }
}