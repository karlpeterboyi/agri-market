<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Commodity;

class CommodityController extends Controller
{
    public function index()
    {
        return Commodity::orderBy('name')->get();
    }
}
