<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgriculturalStatistic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AgriculturalStatisticController extends Controller
{
    /**
     * Public listing / filter of statistics.
     */
    public function index(Request $request)
    {
        $query = AgriculturalStatistic::query();

        if ($request->filled('indicator_code')) {
            $query->where('indicator_code', $request->indicator_code);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('region')) {
            $query->where('region', $request->region);
        }
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }
        if ($request->filled('from_year')) {
            $query->where('year', '>=', $request->from_year);
        }
        if ($request->filled('to_year')) {
            $query->where('year', '<=', $request->to_year);
        }

        return response()->json(
            $query->orderByDesc('year')->orderBy('indicator_code')->paginate(50)
        );
    }

    /**
     * Food security / high-level dashboard indicators.
     */
    public function foodSecurity(Request $request)
    {
        $year = $request->input('year', now()->year);

        $indicators = AgriculturalStatistic::where('category', 'food_security')
            ->where('year', $year)
            ->when($request->filled('region'), fn ($q) => $q->where('region', $request->region))
            ->get();

        $production = AgriculturalStatistic::where('category', 'production')
            ->where('year', $year)
            ->when($request->filled('region'), fn ($q) => $q->where('region', $request->region))
            ->get();

        return response()->json([
            'year' => $year,
            'food_security_indicators' => $indicators,
            'production_indicators' => $production,
        ]);
    }

    /**
     * Time series for a single indicator (for charts).
     */
    public function timeSeries(Request $request)
    {
        $validated = $request->validate([
            'indicator_code' => 'required|string',
            'region' => 'nullable|string',
            'from_year' => 'nullable|integer',
            'to_year' => 'nullable|integer',
        ]);

        $query = AgriculturalStatistic::where('indicator_code', $validated['indicator_code']);

        if (!empty($validated['region'])) {
            $query->where('region', $validated['region']);
        }
        if (!empty($validated['from_year'])) {
            $query->where('year', '>=', $validated['from_year']);
        }
        if (!empty($validated['to_year'])) {
            $query->where('year', '<=', $validated['to_year']);
        }

        $series = $query->orderBy('year')->orderBy('month')->get();

        return response()->json([
            'indicator_code' => $validated['indicator_code'],
            'series' => $series,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeGovernment();

        $validated = $request->validate([
            'indicator_code' => 'required|string|max:100',
            'indicator_name' => 'required|string|max:255',
            'category' => 'required|in:production,prices,food_security,inputs,trade,other',
            'region' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'nullable|integer|min:1|max:12',
            'value' => 'required|numeric',
            'unit' => 'nullable|string|max:50',
            'source' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $stat = AgriculturalStatistic::create($validated);

        return response()->json($stat, 201);
    }

    protected function authorizeGovernment(): void
    {
        if (!in_array(auth()->user()->role ?? '', ['admin', 'government_officer'])) {
            abort(403);
        }
    }
}
