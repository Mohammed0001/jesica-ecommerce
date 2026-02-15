<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AdminCollectionController extends Controller
{
    /**
     * Display a listing of collections
     */
    public function index()
    {
        $collections = Collection::withCount(['products'])
            ->latest()
            ->paginate(20);

        return view('admin.collections.index', compact('collections'));
    }

    /**
     * Show the form for creating a new collection
     */
    public function create()
    {
        return view('admin.collections.create');
    }

    /**
     * Store a newly created collection
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'release_date' => 'required|date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'pdf' => 'nullable|mimes:pdf|max:51200',
            'visible' => 'boolean',
        ]);

        // Only collect attributes we expect to insert
    $data = $request->only(['title', 'description', 'release_date']);
        $data['slug'] = Str::slug($request->title);
    $data['visible'] = $request->boolean('visible', true);

        // Handle image upload
        $imageService = app(ImageService::class);
        if ($request->hasFile('image')) {
            // Save the uploaded file path to the image_path column
            $data['image_path'] = $imageService->compressAndStore($request->file('image'), 'collections');
        }

        $collection = Collection::create($data);

    // Handle images upload (multiple)
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                $path = $imageService->compressAndStore($file, 'collections');
                $collection->images()->create([
                    'path' => $path,
                    'order' => $index,
                ]);
            }
        }

        // Handle PDF upload
        if ($request->hasFile('pdf')) {
            $path = $request->file('pdf')->store('collections/pdfs', 'public');
            $collection->update(['pdf_path' => $path]);
        }

        return redirect()->route('admin.collections.index')
            ->with('success', 'Collection created successfully.');
    }

    /**
     * Display the specified collection
     */
    public function show(Collection $collection)
    {
        $collection->load(['products']);

        return view('admin.collections.show', compact('collection'));
    }

    /**
     * Show the form for editing the specified collection
     */
    public function edit(Collection $collection)
    {
        return view('admin.collections.edit', compact('collection'));
    }

    /**
     * Update the specified collection
     */
    public function update(Request $request, Collection $collection)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'release_date' => 'required|date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'pdf' => 'nullable|mimes:pdf|max:51200',
            'visible' => 'boolean',
        ]);

    $data = $request->only(['title', 'description', 'release_date']);
        $data['slug'] = Str::slug($request->title);
    $data['visible'] = $request->boolean('visible', $collection->getAttribute('visible'));

        // Handle image upload
    $imageService = app(ImageService::class);
    if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($collection->image_path) {
                Storage::disk('public')->delete($collection->image_path);
            }
            $data['image_path'] = $imageService->compressAndStore($request->file('image'), 'collections');
        }

        $collection->update($data);

        // Replace collection images (if provided)
    if ($request->hasFile('images')) {
            // delete old files
            foreach ($collection->images as $img) {
                Storage::disk('public')->delete($img->path);
            }
            $collection->images()->delete();

            // store new ones
            foreach ($request->file('images') as $index => $file) {
                $path = $imageService->compressAndStore($file, 'collections');
                $collection->images()->create([
                    'path' => $path,
                    'order' => $index,
                ]);
            }
        }

        if ($request->hasFile('pdf')) {
            // delete old pdf first
            if ($collection->pdf_path) {
                Storage::disk('public')->delete($collection->pdf_path);
            }
            $path = $request->file('pdf')->store('collections/pdfs', 'public');
            $collection->update(['pdf_path' => $path]);
        }

        return redirect()->route('admin.collections.show', $collection)
            ->with('success', 'Collection updated successfully.');
    }

    /**
     * Remove the specified collection
     */
    public function destroy(Collection $collection)
    {
        // Check if collection has products
        if ($collection->products()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete collection with products. Please move or delete products first.');
        }

        // Delete image_path file if exists
        if ($collection->image_path) {
            Storage::disk('public')->delete($collection->image_path);
        }

        // Delete multiple collection images
        foreach ($collection->images as $img) {
            Storage::disk('public')->delete($img->path);
            $img->delete();
        }

        // Delete collection PDF if exists
        if ($collection->pdf_path) {
            Storage::disk('public')->delete($collection->pdf_path);
        }

        $collection->delete();

        return redirect()->route('admin.collections.index')
            ->with('success', 'Collection deleted successfully.');
    }

    /**
     * Toggle collection visibility
     */
    public function toggleVisibility(Collection $collection)
    {
    $collection->update(['visible' => !$collection->getAttribute('visible')]);

        return redirect()->back()
            ->with('success', 'Collection visibility updated successfully.');
    }
}
