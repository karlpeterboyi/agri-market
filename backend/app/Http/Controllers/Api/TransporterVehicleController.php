<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transporter;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class TransporterVehicleController extends Controller
{
    protected function myTransporter(): Transporter
    {
        $user = auth()->user();
        $t = Transporter::firstOrCreate(
            ['user_id' => $user->id],
            [
                'company_name' => $user->name,
                'contact_person' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email,
            ]
        );

        return $t;
    }

    public function index()
    {
        $t = $this->myTransporter();

        return response()->json([
            'transporter' => $t,
            'vehicles' => Vehicle::where('transporter_id', $t->id)->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        if (!in_array(auth()->user()->role, ['transporter', 'admin'], true)) {
            return response()->json(['message' => 'Only transporters can list trucks.'], 403);
        }

        $data = $request->validate([
            'registration_number' => 'required|string|max:50',
            'name' => 'nullable|string|max:100',
            'vehicle_type' => 'required|string|max:50',
            'capacity' => 'required|numeric|min:0',
            'price_per_km' => 'nullable|numeric|min:0',
            'price_per_mile' => 'nullable|numeric|min:0',
            'base_latitude' => 'nullable|numeric',
            'base_longitude' => 'nullable|numeric',
            'base_region' => 'nullable|string|max:100',
            'base_district' => 'nullable|string|max:100',
            'available' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        $t = $this->myTransporter();

        // Derive km rate from mile if only mile provided
        if (empty($data['price_per_km']) && !empty($data['price_per_mile'])) {
            $data['price_per_km'] = round($data['price_per_mile'] / 0.621371, 2);
        }
        if (empty($data['price_per_mile']) && !empty($data['price_per_km'])) {
            $data['price_per_mile'] = round($data['price_per_km'] * 0.621371, 2);
        }

        $vehicle = Vehicle::create(array_merge($data, [
            'transporter_id' => $t->id,
            'available' => $data['available'] ?? true,
        ]));

        return response()->json(['message' => 'Truck listed', 'vehicle' => $vehicle], 201);
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $t = $this->myTransporter();
        if ((int) $vehicle->transporter_id !== (int) $t->id && auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Not your vehicle'], 403);
        }

        $data = $request->validate([
            'registration_number' => 'sometimes|string|max:50',
            'name' => 'nullable|string|max:100',
            'vehicle_type' => 'sometimes|string|max:50',
            'capacity' => 'sometimes|numeric|min:0',
            'price_per_km' => 'nullable|numeric|min:0',
            'price_per_mile' => 'nullable|numeric|min:0',
            'base_latitude' => 'nullable|numeric',
            'base_longitude' => 'nullable|numeric',
            'base_region' => 'nullable|string|max:100',
            'base_district' => 'nullable|string|max:100',
            'available' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        if (isset($data['price_per_mile']) && empty($data['price_per_km'])) {
            $data['price_per_km'] = round($data['price_per_mile'] / 0.621371, 2);
        }
        if (isset($data['price_per_km']) && empty($data['price_per_mile'])) {
            $data['price_per_mile'] = round($data['price_per_km'] * 0.621371, 2);
        }

        $vehicle->update($data);

        return response()->json(['message' => 'Updated', 'vehicle' => $vehicle->fresh()]);
    }

    public function destroy(Vehicle $vehicle)
    {
        $t = $this->myTransporter();
        if ((int) $vehicle->transporter_id !== (int) $t->id && auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Not your vehicle'], 403);
        }
        $vehicle->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
