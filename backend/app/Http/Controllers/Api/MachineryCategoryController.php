<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MachineryCategory;

class MachineryCategoryController extends Controller
{
    public function index()
    {
        return MachineryCategory::orderBy('name')->get();
    }

    public function show(MachineryCategory $machineryCategory)
    {
        return $machineryCategory;
    }
}