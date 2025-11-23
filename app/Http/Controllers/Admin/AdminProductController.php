<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AdminProductController extends Controller
{
    /**
     * Display a listing of products
     */
    public function index()
    {
        $products = Product::with(['collection'])
            ->latest()
            ->paginate(20);

        return view('admin.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new product
     */
    public function create()
    {
        $collections = Collection::all();

        return view('admin.products.create', compact('collections'));
    }

    /**
     * Store a newly created product
     */
    public function store(Request $request)
    {
        $currencies = implode(',', array_keys(config('currencies.rates')));
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'collection_id' => 'required|exists:collections,id',
            'currency' => "required|string|in:$currencies",
            'sku' => 'nullable|string|max:255',
            'quantity' => 'nullable|integer|min:0',
            'is_one_of_a_kind' => 'boolean',
            'size_chart_id' => 'nullable|exists:size_charts,id',
            'story' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'visible' => 'boolean',
        ]);

    $data = $request->only(['title', 'description', 'price', 'currency', 'collection_id', 'sku', 'quantity', 'is_one_of_a_kind', 'size_chart_id', 'story']);
        $data['slug'] = Str::slug($request->title);
        $data['visible'] = $request->boolean('visible', true);
        $data['quantity'] = $request->input('quantity', 1);
        $data['is_one_of_a_kind'] = $request->boolean('is_one_of_a_kind', false);

        // Handle image upload
        // product images are managed via ProductImage; admin UI will handle this separately

        $product = Product::create($data);

        // Handle multiple image uploads for newly created product
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                $path = $file->store('products', 'public');
                $product->images()->create(['path' => $path, 'order' => $index]);
            }
        } elseif ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $product->images()->create(['path' => $path, 'order' => 0]);
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified product
     */
    public function show(Product $product)
    {
        $product->load(['collection']);

        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product
     */
    public function edit(Product $product)
    {
        $collections = Collection::all();

        return view('admin.products.edit', compact('product', 'collections'));
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, Product $product)
    {
        $currencies = implode(',', array_keys(config('currencies.rates')));
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'collection_id' => 'required|exists:collections,id',
            'currency' => "required|string|in:$currencies",
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'visible' => 'boolean',
            'sku' => 'nullable|string|max:255',
            'quantity' => 'nullable|integer|min:0',
            'is_one_of_a_kind' => 'boolean',
            'size_chart_id' => 'nullable|exists:size_charts,id',
            'story' => 'nullable|string',
        ]);

    $data = $request->only(['title', 'description', 'price', 'currency', 'collection_id', 'sku', 'quantity', 'is_one_of_a_kind', 'size_chart_id', 'story']);
        $data['slug'] = Str::slug($request->title);
    $data['visible'] = $request->boolean('visible', $product->getAttribute('visible'));
    $data['is_one_of_a_kind'] = $request->boolean('is_one_of_a_kind', $product->getAttribute('is_one_of_a_kind'));

        // Handle image upload
        if ($request->hasFile('images')) {
            // delete all existing images
            foreach ($product->images as $img) {
                Storage::disk('public')->delete($img->path);
                $img->delete();
            }
            foreach ($request->file('images') as $index => $file) {
                $path = $file->store('products', 'public');
                $product->images()->create(['path' => $path, 'order' => $index]);
            }
        } elseif ($request->hasFile('image')) {
            // Replace first image only
            $firstImage = $product->images()->orderBy('order')->first();
            if ($firstImage) {
                Storage::disk('public')->delete($firstImage->path);
                $firstImage->delete();
            }

            $path = $request->file('image')->store('products', 'public');
            $product->images()->create(['path' => $path, 'order' => 0]);
        }

        $product->update($data);

        return redirect()->route('admin.products.show', $product)
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified product
     */
    public function destroy(Product $product)
    {
        // Delete product images files and records if exist
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->path);
            $image->delete();
        }

        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    /**
     * Toggle product visibility
     */
    public function toggleVisibility(Product $product)
    {
    $product->update(['visible' => !$product->getAttribute('visible')]);

        return redirect()->back()
            ->with('success', 'Product visibility updated successfully.');
    }
}
