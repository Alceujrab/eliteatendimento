<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $query = Vehicle::where('tenant_id', $tenantId);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('brand', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('plate', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }

        if ($request->filled('fuel_type')) {
            $query->where('fuel_type', $request->fuel_type);
        }

        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->price_min);
        }

        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->price_max);
        }

        $vehicles = $query->latest()->paginate(15);

        $brands = Vehicle::where('tenant_id', $tenantId)->distinct()->pluck('brand')->sort();

        $statusCounts = Vehicle::where('tenant_id', $tenantId)
            ->selectRaw("status, count(*) as total")
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('vehicles.index', compact('vehicles', 'brands', 'statusCounts'));
    }

    public function create()
    {
        return view('vehicles.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'version' => 'nullable|string|max:100',
            'year_manufacture' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'year_model' => 'required|integer|min:1900|max:' . (date('Y') + 2),
            'color' => 'nullable|string|max:50',
            'fuel_type' => 'required|in:flex,gasoline,ethanol,diesel,electric,hybrid',
            'transmission' => 'required|in:manual,automatic,cvt,automated',
            'mileage' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'fipe_price' => 'nullable|numeric|min:0',
            'plate' => 'nullable|string|max:10',
            'chassis' => 'nullable|string|max:30',
            'renavam' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'features' => 'nullable|array',
            'status' => 'required|in:available,reserved,sold',
        ]);

        $data['tenant_id'] = auth()->user()->tenant_id;

        // Handle photos upload
        if ($request->hasFile('photos')) {
            $photos = [];
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('vehicles', 'public');
                $photos[] = $path;
            }
            $data['photos'] = $photos;
        }

        $vehicle = Vehicle::create($data);

        return redirect()->route('vehicles.show', $vehicle)->with('success', 'Veículo cadastrado.');
    }

    public function show(Vehicle $vehicle)
    {
        $vehicle->load(['appointments.contact']);

        return view('vehicles.show', compact('vehicle'));
    }

    public function edit(Vehicle $vehicle)
    {
        return view('vehicles.edit', compact('vehicle'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $data = $request->validate([
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'version' => 'nullable|string|max:100',
            'year_manufacture' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'year_model' => 'required|integer|min:1900|max:' . (date('Y') + 2),
            'color' => 'nullable|string|max:50',
            'fuel_type' => 'required|in:flex,gasoline,ethanol,diesel,electric,hybrid',
            'transmission' => 'required|in:manual,automatic,cvt,automated',
            'mileage' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'fipe_price' => 'nullable|numeric|min:0',
            'plate' => 'nullable|string|max:10',
            'chassis' => 'nullable|string|max:30',
            'renavam' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'features' => 'nullable|array',
            'status' => 'required|in:available,reserved,sold',
        ]);

        // Handle photos upload
        if ($request->hasFile('photos')) {
            $photos = $vehicle->photos ?? [];
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('vehicles', 'public');
                $photos[] = $path;
            }
            $data['photos'] = $photos;
        }

        $vehicle->update($data);

        return redirect()->route('vehicles.show', $vehicle)->with('success', 'Veículo atualizado.');
    }

    public function updateStatus(Request $request, Vehicle $vehicle)
    {
        $request->validate(['status' => 'required|in:available,reserved,sold']);

        $vehicle->update(['status' => $request->status]);

        return back()->with('success', 'Status atualizado.');
    }
}
