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
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'collection_id' => 'required|exists:collections,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'visible' => 'boolean',
        ]);

        $data = $request->only(['title', 'description', 'price', 'collection_id', 'sku', 'quantity', 'is_one_of_a_kind', 'size_chart_id']);
        $data['slug'] = Str::slug($request->title);
        $data['visible'] = $request->boolean('visible', true);

        // Handle image upload
        // product images are managed via ProductImage; admin UI will handle this separately

        Product::create($data);

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
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'collection_id' => 'required|exists:collections,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'visible' => 'boolean',
        ]);

        $data = $request->only(['title', 'description', 'price', 'collection_id', 'sku', 'quantity', 'is_one_of_a_kind', 'size_chart_id']);
        $data['slug'] = Str::slug($request->title);
        $data['visible'] = $request->boolean('visible', $product->visible);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            // If the admin uploaded a primary image, create a ProductImage record or replace main image
            // For now, upload to storage and create or update the first product image entry
            $path = $request->file('image')->store('products', 'public');
            // create a new product image record (this assumes ProductImage model exists)
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
        // Delete image if exists
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
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
        $product->update(['is_visible' => !$product->is_visible]);

        return redirect()->back()
            ->with('success', 'Product visibility updated successfully.');
    }
}
