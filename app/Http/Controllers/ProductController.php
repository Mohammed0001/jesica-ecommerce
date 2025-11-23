<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display all products with filtering and pagination
     */
    public function index(Request $request)
    {
        $query = Product::visible()
            ->with(['collection', 'images']);

        // Apply filters
        if ($request->filled('collection')) {
            $query->whereHas('collection', function ($q) use ($request) {
                $q->where('slug', $request->collection);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'price_low':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_high':
                    $query->orderBy('price', 'desc');
                    break;
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'name':
                    $query->orderBy('title', 'asc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $products = $query->paginate(12);

        // Get collections for filtering
        $collections = \App\Models\Collection::visible()
            ->orderBy('title')
            ->get();

        return view('products.index', compact('products', 'collections'));
    }

    /**
     * Display the specified product
     */
    public function show(Product $product)
    {
        // Check if product is visible
        if (!$product->getAttribute('visible') || ($product->collection && !$product->collection->getAttribute('visible'))) {
            abort(404);
        }

        $product->load([
            'collection',
            'images',
            'sizes'
        ]);

        // Get related products from the same collection or similar products
        $relatedProducts = collect();

        if ($product->collection_id) {
            $relatedProducts = Product::visible()
                ->where('collection_id', $product->collection_id)
                ->where('id', '!=', $product->id)
                ->with(['images'])
                ->take(4)
                ->get();
        }

        // If we don't have enough related products, get some random ones
        if ($relatedProducts->count() < 4) {
            $additionalProducts = Product::visible()
                ->where('id', '!=', $product->id)
                ->whereNotIn('id', $relatedProducts->pluck('id'))
                ->with(['images'])
                ->inRandomOrder()
                ->take(4 - $relatedProducts->count())
                ->get();

            $relatedProducts = $relatedProducts->merge($additionalProducts);
        }

        return view('products.show', compact('product', 'relatedProducts'));
    }

    /**
     * Get size chart data for AJAX requests
     */
    public function sizeChart(Product $product)
    {
        if (!$product->sizeChart) {
            return response()->json(['error' => 'No size chart available'], 404);
        }

        return response()->json([
            'name' => $product->sizeChart->name,
            'measurements' => $product->sizeChart->measurements,
            'image_url' => $product->sizeChart->image_url,
        ]);
    }
}
