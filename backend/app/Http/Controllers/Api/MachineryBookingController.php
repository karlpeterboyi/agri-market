<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MachineryBooking;
use App\Models\MachineryListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MachineryBookingController extends Controller
{
    /**
     * Customer bookings
     */
    public function index()
    {
        return MachineryBooking::with([
            'listing',
            'customer',
            'owner'
        ])
        ->where('customer_id', Auth::id())
        ->latest()
        ->get();
    }

    /**
     * Create booking
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'machinery_listing_id' => 'required|exists:machinery_listings,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'operator_required' => 'boolean',
            'notes' => 'nullable|string'
        ]);

        $listing = MachineryListing::findOrFail($data['machinery_listing_id']);

        $days = max(1, now()->parse($data['start_date'])
            ->diffInDays(now()->parse($data['end_date'])) + 1);

        $data['customer_id'] = Auth::id();
        $data['owner_id'] = $listing->owner_id;
        $data['total_price'] = ($listing->rental_price ?? 0) * $days;
        $data['payment_status'] = 'pending';
        $data['status'] = 'pending';

        return MachineryBooking::create($data);
    }

    /**
     * Booking details
     */
    public function show(MachineryBooking $machineryBooking)
    {
        return $machineryBooking->load([
            'listing',
            'customer',
            'owner'
        ]);
    }

    /**
     * Owner accepts booking
     */
    public function accept(MachineryBooking $machineryBooking)
    {
        abort_if(Auth::id() != $machineryBooking->owner_id, 403);

        $machineryBooking->update([
            'status' => 'accepted'
        ]);

        return $machineryBooking;
    }

    /**
     * Owner rejects booking
     */
    public function reject(MachineryBooking $machineryBooking)
    {
        abort_if(Auth::id() != $machineryBooking->owner_id, 403);

        $machineryBooking->update([
            'status' => 'rejected'
        ]);

        return $machineryBooking;
    }

    /**
     * Machine dispatched
     */
    public function dispatch(MachineryBooking $machineryBooking)
    {
        abort_if(Auth::id() != $machineryBooking->owner_id, 403);

        $machineryBooking->update([
            'status' => 'dispatched'
        ]);

        return $machineryBooking;
    }

    /**
     * Rental started
     */
    public function start(MachineryBooking $machineryBooking)
    {
        abort_if(Auth::id() != $machineryBooking->owner_id, 403);

        $machineryBooking->update([
            'status' => 'running'
        ]);

        return $machineryBooking;
    }

    /**
     * Rental completed
     */
    public function complete(MachineryBooking $machineryBooking)
    {
        abort_if(Auth::id() != $machineryBooking->owner_id, 403);

        $machineryBooking->update([
            'status' => 'completed'
        ]);

        $machineryBooking->listing()->update([
            'available' => true
        ]);

        return $machineryBooking;
    }

    /**
     * Customer cancels booking
     */
    public function cancel(MachineryBooking $machineryBooking)
    {
        abort_if(Auth::id() != $machineryBooking->customer_id, 403);

        $machineryBooking->update([
            'status' => 'cancelled'
        ]);

        return $machineryBooking;
    }

    /**
     * Owner bookings
     */
    public function ownerBookings()
    {
        return MachineryBooking::with([
            'listing',
            'customer'
        ])
        ->where('owner_id', Auth::id())
        ->latest()
        ->get();
    }
}