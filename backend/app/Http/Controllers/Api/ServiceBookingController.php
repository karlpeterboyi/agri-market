<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceBooking;
use App\Models\ServiceQuote;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceBookingController extends Controller
{
    /**
     * Customer Books a Quoted Service
     */
    public function store(Request $request)
    {
        $validated = $request->validate([

            'service_quote_id' => 'required|exists:service_quotes,id',

            'booking_date' => 'required|date',

            'booking_time' => 'nullable',

            'region' => 'required|string',

            'district' => 'required|string',

            'farm_location' => 'required|string',

            'quantity' => 'nullable|numeric',

            'unit' => 'nullable|string',

            'notes' => 'nullable|string',

        ]);

        $quote = ServiceQuote::findOrFail($validated['service_quote_id']);

        $booking = ServiceBooking::create([

            'service_quote_id' => $quote->id,

            'customer_id' => auth()->id(),

            'provider_id' => $quote->provider_id,

            'booking_reference' =>
                'SB-' .
                now()->format('Ymd') .
                '-' .
                strtoupper(Str::random(6)),

            'booking_date' => $validated['booking_date'],

            'booking_time' => $validated['booking_time'] ?? null,

            'scheduled_date' => $validated['booking_date'],

            'region' => $validated['region'],

            'district' => $validated['district'],

            'farm_location' => $validated['farm_location'],

            'quantity' => $validated['quantity'] ?? null,

            'unit' => $validated['unit'] ?? null,

            'total_price' => $quote->quoted_price,

            'notes' => $validated['notes'] ?? null,

            'status' => ServiceBooking::STATUS_PENDING,

            'payment_status' => ServiceBooking::PAYMENT_PENDING,

        ]);

        return response()->json($booking, 201);
    }

    /**
     * Customer Bookings
     */
    public function index()
    {
        return ServiceBooking::with([
            'quote.service.category',
            'provider',
            'customer'
        ])
        ->where('customer_id', auth()->id())
        ->latest()
        ->paginate(20);
    }

    /**
     * Single Booking
     */
    public function show($id)
    {
        return ServiceBooking::with([
            'quote.service.category',
            'provider',
            'customer'
        ])->findOrFail($id);
    }

    /**
     * Provider Bookings
     */
    public function providerBookings()
    {
        return ServiceBooking::with([
            'quote.service.category',
            'customer'
        ])
        ->where('provider_id', auth()->id())
        ->latest()
        ->paginate(20);
    }

    /**
     * Accept
     */
    public function accept($id)
    {
        $booking = ServiceBooking::findOrFail($id);

        $booking->update([
            'status' => ServiceBooking::STATUS_ACCEPTED
        ]);

        return response()->json([
            'message' => 'Booking accepted.',
            'booking' => $booking
        ]);
    }

    /**
     * Reject
     */
    public function reject($id)
    {
        $booking = ServiceBooking::findOrFail($id);

        $booking->update([
            'status' => ServiceBooking::STATUS_REJECTED
        ]);

        return response()->json([
            'message' => 'Booking rejected.',
            'booking' => $booking
        ]);
    }

    /**
     * Provider On The Way
     */
    public function onTheWay($id)
    {
        $booking = ServiceBooking::findOrFail($id);

        $booking->update([
            'status' => ServiceBooking::STATUS_ON_THE_WAY
        ]);

        return response()->json([
            'message' => 'Provider is on the way.',
            'booking' => $booking
        ]);
    }

    /**
     * Work Started
     */
    public function start($id)
    {
        $booking = ServiceBooking::findOrFail($id);

        $booking->update([
            'status' => ServiceBooking::STATUS_IN_PROGRESS
        ]);

        return response()->json([
            'message' => 'Service started.',
            'booking' => $booking
        ]);
    }

    /**
     * Complete
     */
    public function complete($id)
    {
        $booking = ServiceBooking::findOrFail($id);

        $booking->update([

            'status' => ServiceBooking::STATUS_COMPLETED,

            'completed_date' => now()

        ]);

        return response()->json([
            'message' => 'Booking completed.',
            'booking' => $booking
        ]);
    }

    /**
     * Cancel
     */
    public function cancel($id)
    {
        $booking = ServiceBooking::findOrFail($id);

        $booking->update([
            'status' => ServiceBooking::STATUS_CANCELLED
        ]);

        return response()->json([
            'message' => 'Booking cancelled.',
            'booking' => $booking
        ]);
    }
}