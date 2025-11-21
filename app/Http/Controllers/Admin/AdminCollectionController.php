<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collection;
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'visible' => 'boolean',
        ]);

        // Only collect attributes we expect to insert
        $data = $request->only(['title', 'description']);
        $data['slug'] = Str::slug($request->title);
        $data['visible'] = $request->boolean('visible', true);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Save the uploaded file path to the image_path column
            $data['image_path'] = $request->file('image')->store('collections', 'public');
        }

    Collection::create($data);

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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'visible' => 'boolean',
        ]);

        $data = $request->only(['title', 'description']);
        $data['slug'] = Str::slug($request->title);
    $data['visible'] = $request->boolean('visible', $collection->getAttribute('visible'));

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($collection->image_path) {
                Storage::disk('public')->delete($collection->image_path);
            }
            $data['image_path'] = $request->file('image')->store('collections', 'public');
        }

        $collection->update($data);

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

        // Delete image if exists
        if ($collection->image_path) {
            Storage::disk('public')->delete($collection->image_path);
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
