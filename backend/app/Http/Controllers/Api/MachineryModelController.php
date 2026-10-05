<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MachineryModel;
use Illuminate\Http\Request;

class MachineryModelController extends Controller
{
    public function index(Request $request)
    {
        $query = MachineryModel::with([
            'brand',
            'category'
        ]);

        if ($request->filled('brand')) {
            $query->where('machinery_brand_id', $request->brand);
        }

        if ($request->filled('category')) {
            $query->where('machinery_category_id', $request->category);
        }

        return $query
            ->orderBy('name')
            ->get();
    }

    public function show(MachineryModel $machineryModel)
    {
        return $machineryModel->load([
            'brand',
            'category'
        ]);
    }
}