<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SizeChart;
use Illuminate\Support\Facades\Storage;

class AdminSizeChartController extends Controller
{
    public function index()
    {
        $sizeCharts = SizeChart::latest()->paginate(20);
        return view('admin.size_charts.index', compact('sizeCharts'));
    }

    public function create()
    {
        return view('admin.size_charts.create');
    }

    public function store(Request $request)
    {
        // Accept measurements as nullable string (JSON from textarea) or array
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
            'measurements' => 'nullable',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('size_charts', 'public');
            $data['image_path'] = $path;
        }

        // measurements can be submitted as JSON via a textarea or JS; accept raw input
        $measurementsInput = $request->input('measurements');
        if (is_array($measurementsInput)) {
            $data['measurements'] = $measurementsInput;
        } elseif (is_string($measurementsInput) && trim($measurementsInput) !== '') {
            $decoded = json_decode($measurementsInput, true);
            $data['measurements'] = is_array($decoded) ? $decoded : [];
        } else {
            // Ensure measurements is never null to satisfy DB NOT NULL constraint
            $data['measurements'] = [];
        }

        SizeChart::create($data);

        return redirect()->route('admin.size-charts.index')->with('success', 'Size chart created.');
    }

    public function destroy(SizeChart $sizeChart)
    {
        if ($sizeChart->image_path) {
            Storage::disk('public')->delete($sizeChart->image_path);
        }
        $sizeChart->delete();
        return redirect()->route('admin.size-charts.index')->with('success', 'Size chart deleted.');
    }
}
