<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCropCalendarRequest;
use App\Http\Resources\CropCalendarResource;
use App\Models\CropCalendar;
use App\Services\Climate\CropCalendarService;

class CropCalendarController extends Controller
{
    public function index()
    {
        return CropCalendarResource::collection(

            CropCalendar::with('activities')

                ->where('user_id', auth()->id())

                ->get()

        );
    }

    public function store(
        StoreCropCalendarRequest $request,
        CropCalendarService $service
    ) {

        $calendar = CropCalendar::create([

            ...$request->validated(),

            'user_id'=>auth()->id(),

        ]);

        $service->generate($calendar);

        return new CropCalendarResource(

            $calendar->load('activities')

        );
    }
}