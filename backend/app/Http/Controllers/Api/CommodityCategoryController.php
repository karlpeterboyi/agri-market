<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CommodityCategory;

class CommodityCategoryController extends Controller
{
    public function index()
    {
        return CommodityCategory::withCount('commodities')
            ->where('active', true)
            ->orderBy('name')
            ->get();
    }
}