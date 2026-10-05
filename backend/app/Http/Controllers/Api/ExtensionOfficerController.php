<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExtensionOfficer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExtensionOfficerController extends Controller
{
    /**
     * GET /api/extension-officers
     * Supports filtering by region, district, or profession.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ExtensionOfficer::with(['user:id,name,email', 'institution:id,name,acronym'])
            ->where('verified', true);

        if ($request->has('region')) {
            $query->where('region', $request->region);
        }

        if ($request->has('district')) {
            $query->where('district', $request->district);
        }

        if ($request->has('profession')) {
            $query->where('profession', $request->profession);
        }

        $officers = $query->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $officers,
        ]);
    }

    /**
     * GET /api/extension-officers/{id}
     */
    public function show(int $id): JsonResponse
    {
        $officer = ExtensionOfficer::with([
            'user:id,name,email',
            'institution:id,name,acronym',
        ])->find($id);

        if (!$officer) {
            return response()->json(['success' => false, 'message' => 'Extension Officer not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $officer,
        ]);
    }
}
