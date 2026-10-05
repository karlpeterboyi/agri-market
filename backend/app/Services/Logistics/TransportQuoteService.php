<?php

namespace App\Services\Logistics;

use App\Models\Order;
use App\Models\ProductListing;
use App\Models\TransportQuote;
use App\Models\Vehicle;
use App\Services\GIS\DistanceService;
use Illuminate\Support\Facades\Schema;

class TransportQuoteService
{
    public function __construct(protected DistanceService $distance)
    {
    }

    public function quoteForOrder(Order $order, array $opts = []): array
    {
        $listing = null;
        if (!empty($order->listing_id) && class_exists(ProductListing::class)) {
            $listing = ProductListing::find($order->listing_id);
        }

        $pickup = $this->distance->resolvePoint(
            isset($opts['pickup_lat']) ? (float) $opts['pickup_lat'] : ($listing->latitude ?? null),
            isset($opts['pickup_lng']) ? (float) $opts['pickup_lng'] : ($listing->longitude ?? null),
            $opts['pickup_region'] ?? ($listing->region ?? null)
        );

        $drop = $this->distance->resolvePoint(
            isset($opts['dropoff_lat']) ? (float) $opts['dropoff_lat'] : null,
            isset($opts['dropoff_lng']) ? (float) $opts['dropoff_lng'] : null,
            $opts['delivery_region'] ?? ($order->delivery_region ?? null)
        );

        if (!$drop) {
            $drop = $this->distance->resolvePoint(
                $order->delivery_latitude ?? null,
                $order->delivery_longitude ?? null,
                $order->delivery_region ?? $order->buyer_region ?? null
            );
        }

        if (!$pickup || !$drop) {
            return [
                'distance_km' => 0,
                'distance_miles' => 0,
                'message' => 'Could not resolve pickup/dropoff. Set listing GPS or region names.',
                'quotes' => [],
            ];
        }

        $tripKm = $this->distance->haversineKm($pickup['lat'], $pickup['lng'], $drop['lat'], $drop['lng']);
        $vehicles = Vehicle::query()->where('available', true)->with('transporter')->get();
        $quotes = [];

        foreach ($vehicles as $v) {
            $rate = (float) ($v->price_per_km ?: 0);
            if ($rate <= 0 && $v->price_per_mile) {
                $rate = (float) $v->price_per_mile / 0.621371;
            }
            if ($rate <= 0) {
                continue;
            }

            $baseLat = $v->base_latitude ?? $v->transporter?->base_latitude;
            $baseLng = $v->base_longitude ?? $v->transporter?->base_longitude;
            $emptyKm = 0.0;
            if ($baseLat && $baseLng) {
                $emptyKm = $this->distance->haversineKm((float) $baseLat, (float) $baseLng, $pickup['lat'], $pickup['lng']);
            }

            $billableKm = $tripKm + ($emptyKm * 0.5);
            $total = round($billableKm * $rate, 0);

            $quotes[] = [
                'transporter_id' => $v->transporter_id,
                'transporter_name' => $v->transporter?->company_name ?? $v->transporter?->contact_person,
                'vehicle_id' => $v->id,
                'vehicle_name' => $v->name ?: $v->registration_number,
                'vehicle_type' => $v->vehicle_type,
                'capacity' => $v->capacity,
                'price_per_km' => $rate,
                'price_per_mile' => round($rate * 0.621371, 2),
                'trip_km' => $tripKm,
                'empty_km' => $emptyKm,
                'billable_km' => round($billableKm, 2),
                'total_cost' => $total,
                'currency' => 'TZS',
            ];

            if (Schema::hasTable('transport_quotes') && class_exists(TransportQuote::class)) {
                TransportQuote::updateOrCreate(
                    ['order_id' => $order->id, 'vehicle_id' => $v->id],
                    [
                        'transporter_id' => $v->transporter_id,
                        'distance_km' => $billableKm,
                        'price_per_km' => $rate,
                        'total_cost' => $total,
                        'pickup_lat' => $pickup['lat'],
                        'pickup_lng' => $pickup['lng'],
                        'dropoff_lat' => $drop['lat'],
                        'dropoff_lng' => $drop['lng'],
                        'status' => 'suggested',
                    ]
                );
            }
        }

        usort($quotes, fn ($a, $b) => $a['total_cost'] <=> $b['total_cost']);

        return [
            'distance_km' => $tripKm,
            'distance_miles' => $this->distance->kmToMiles($tripKm),
            'pickup' => $pickup,
            'dropoff' => $drop,
            'quotes' => array_slice($quotes, 0, (int) ($opts['limit'] ?? 10)),
        ];
    }
}
