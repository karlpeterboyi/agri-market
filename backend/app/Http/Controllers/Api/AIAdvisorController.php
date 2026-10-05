<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AIRecommendation;
use App\Models\Farm;
use Illuminate\Http\Request;

class AIAdvisorController extends Controller
{
    /**
     * List AI recommendations for the authenticated farmer's farms.
     */
    public function index(Request $request)
    {
        $query = AIRecommendation::query()
            ->whereHas('farm', fn ($q) => $q->where('owner_id', auth()->id()));

        if ($request->filled('farm_id')) {
            $query->where('farm_id', $request->farm_id);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('accepted')) {
            $query->where('accepted', $request->boolean('accepted'));
        }

        return response()->json(
            $query->latest()->paginate(20)
        );
    }

    /**
     * Generate a simple rule-based recommendation (placeholder for real AI).
     * In production this would call OpenAI / local model / TensorFlow service.
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'farm_id' => 'required|exists:farms,id',
            'category' => 'nullable|string|max:100', // crop, livestock, pest, soil, weather, market
            'context' => 'nullable|array', // extra inputs (crop, stage, symptoms, etc.)
        ]);

        $farm = Farm::where('id', $validated['farm_id'])
            ->where('owner_id', auth()->id())
            ->firstOrFail();

        $category = $validated['category'] ?? 'general';
        $context = $validated['context'] ?? [];

        // Simple rule-based demo recommendations (replace with real AI later)
        $recommendation = $this->buildRecommendation($category, $context, $farm);

        $record = AIRecommendation::create([
            'farm_id' => $farm->id,
            'category' => $category,
            'priority' => $recommendation['priority'],
            'title' => $recommendation['title'],
            'recommendation' => $recommendation['text'],
            'inputs' => array_merge($context, [
                'farm_name' => $farm->name,
                'region' => $farm->region,
            ]),
            'confidence' => $recommendation['confidence'],
            'accepted' => false,
        ]);

        return response()->json($record, 201);
    }

    public function accept(AIRecommendation $aiRecommendation)
    {
        $this->authorizeOwnership($aiRecommendation);

        $aiRecommendation->update([
            'accepted' => true,
            'accepted_at' => now(),
        ]);

        return response()->json([
            'message' => 'Recommendation accepted.',
            'data' => $aiRecommendation,
        ]);
    }

    public function show(AIRecommendation $aiRecommendation)
    {
        $this->authorizeOwnership($aiRecommendation);
        return response()->json($aiRecommendation->load('farm'));
    }

    protected function buildRecommendation(string $category, array $context, Farm $farm): array
    {
        $crop = $context['crop'] ?? 'maize';
        $region = $farm->region ?? 'Tanzania';

        $templates = [
            'pest' => [
                'title' => 'Pest scouting recommended',
                'text' => "For {$crop} in {$region}, regular field scouting every 3–5 days during the vegetative stage is advised. Look for fall armyworm, aphids and stem borers. Consider neem-based or recommended IPM options if thresholds are reached.",
                'priority' => 'high',
                'confidence' => 0.78,
            ],
            'soil' => [
                'title' => 'Soil health check',
                'text' => "Soil testing is recommended before the next season for your farm in {$region}. Focus on pH, organic matter, N-P-K and micronutrients. Based on typical soils in the area, liming or organic manure may improve yields.",
                'priority' => 'medium',
                'confidence' => 0.72,
            ],
            'weather' => [
                'title' => 'Weather-aware operations',
                'text' => "Monitor short-range forecasts for {$region}. Avoid spraying or fertiliser application if heavy rain is expected within 24 hours. Adjust irrigation schedules accordingly.",
                'priority' => 'medium',
                'confidence' => 0.70,
            ],
            'market' => [
                'title' => 'Market timing tip',
                'text' => "Current seasonal patterns suggest checking MkulimaHub marketplace prices for {$crop} before harvest. Consider staggered harvesting or storage if prices are expected to rise.",
                'priority' => 'low',
                'confidence' => 0.65,
            ],
            'general' => [
                'title' => 'General farm advisory',
                'text' => "Keep activity records up to date on MkulimaHub. Link crop cycles to field blocks and log costs so you can calculate profitability per enterprise. Consider enrolling in a short training course on record keeping.",
                'priority' => 'low',
                'confidence' => 0.60,
            ],
        ];

        return $templates[$category] ?? $templates['general'];
    }

    protected function authorizeOwnership(AIRecommendation $rec): void
    {
        if ($rec->farm->owner_id !== auth()->id()) {
            abort(403);
        }
    }
}
