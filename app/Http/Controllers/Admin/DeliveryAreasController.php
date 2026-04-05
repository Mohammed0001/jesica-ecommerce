<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\Request;

class DeliveryAreasController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * List all delivery areas
     */
    public function index()
    {
        $areas = Region::orderBy('name')->get();
        return view('admin.delivery_areas.index', compact('areas'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('admin.delivery_areas.form', ['area' => null]);
    }

    /**
     * Store new delivery area
     */
    public function store(Request $request)
    {
        $data = $this->validated($request);
        Region::create($data);

        return redirect()->route('admin.delivery-areas.index')
            ->with('success', 'Delivery area created successfully.');
    }

    /**
     * Show edit form
     */
    public function edit(Region $deliveryArea)
    {
        return view('admin.delivery_areas.form', ['area' => $deliveryArea]);
    }

    /**
     * Update existing delivery area
     */
    public function update(Request $request, Region $deliveryArea)
    {
        $data = $this->validated($request);
        $deliveryArea->update($data);

        return redirect()->route('admin.delivery-areas.index')
            ->with('success', 'Delivery area updated successfully.');
    }

    /**
     * Delete a delivery area
     */
    public function destroy(Region $deliveryArea)
    {
        $deliveryArea->delete();

        return redirect()->route('admin.delivery-areas.index')
            ->with('success', 'Delivery area deleted.');
    }

    /**
     * Validate and transform request data
     */
    private function validated(Request $request): array
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'type'         => 'required|in:cairo_district,governorate',
            'delivery_fee' => 'required|numeric|min:0',
            'city_names'   => 'required|string',
        ]);

        // Convert comma-separated city names to array, trimming whitespace
        $cityNames = array_filter(
            array_map('trim', explode(',', $request->city_names)),
            fn($v) => $v !== ''
        );

        return [
            'name'         => $request->name,
            'type'         => $request->type,
            'delivery_fee' => $request->delivery_fee,
            'city_names'   => array_values($cityNames),
        ];
    }
}
