<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCropRequest;
use App\Http\Requests\UpdateCropRequest;
use App\Http\Resources\CropResource;
use App\Models\Crop;

class CropController extends Controller
{
    public function index()
    {
        return CropResource::collection(

            Crop::withCount([
                'varieties',
                'growthStages',
            ])->paginate()

        );
    }

    public function store(StoreCropRequest $request)
    {
        $crop = Crop::create(
            $request->validated()
        );

        return new CropResource($crop);
    }

    public function show(Crop $crop)
    {
        return new CropResource(
            $crop->load([
                'varieties',
                'growthStages',
                'rules',
            ])
        );
    }

    public function update(
        UpdateCropRequest $request,
        Crop $crop
    ) {
        $crop->update(
            $request->validated()
        );

        return new CropResource($crop);
    }

    public function destroy(Crop $crop)
    {
        $crop->delete();

        return response()->json([
            'message' => 'Crop archived successfully.',
        ]);
    }
}