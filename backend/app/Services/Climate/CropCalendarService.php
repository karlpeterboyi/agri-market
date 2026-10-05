<?php

namespace App\Services\Climate;

use App\Models\CropCalendar;
use App\Models\CropCalendarActivity;

class CropCalendarService
{
    public function generate(
        CropCalendar $calendar
    )
    {
        $activities = [

            [
                'day'=>0,
                'activity'=>'Planting',
            ],

            [
                'day'=>14,
                'activity'=>'First Weeding',
            ],

            [
                'day'=>21,
                'activity'=>'Top Dressing',
            ],

            [
                'day'=>35,
                'activity'=>'Disease Inspection',
            ],

            [
                'day'=>45,
                'activity'=>'Pest Monitoring',
            ],

            [
                'day'=>90,
                'activity'=>'Harvest',
            ],

        ];

        foreach ($activities as $activity) {

            CropCalendarActivity::create([

                'crop_calendar_id'=>$calendar->id,

                'activity'=>$activity['activity'],

                'scheduled_date'=>

                    $calendar
                        ->planting_date
                        ->copy()
                        ->addDays($activity['day']),

            ]);

        }
    }
}