<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Collection;

class SearchController extends Controller
{
    /**
     * Display search results.
     */
    public function index(Request $request)
    {
        $query = $request->get('q', '');
        $products = collect();
        $collections = collect();

        if (strlen($query) >= 2) {
            // Search products
            $products = Product::where('is_visible', true)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                      ->orWhere('description', 'LIKE', "%{$query}%");
                })
                ->with('collection')
                ->take(10)
                ->get();

            // Search collections
            $collections = Collection::where('is_visible', true)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                      ->orWhere('description', 'LIKE', "%{$query}%");
                })
                ->withCount('products')
                ->take(5)
                ->get();
        }

        if ($request->ajax()) {
            return response()->json([
                'products' => $products,
                'collections' => $collections,
                'query' => $query
            ]);
        }

        return view('search.index', compact('products', 'collections', 'query'));
    }

    /**
     * Get search suggestions (AJAX endpoint).
     */
    public function suggestions(Request $request)
    {
        $query = $request->get('q', '');
        $suggestions = [];

        if (strlen($query) >= 2) {
            // Get product suggestions
            $productSuggestions = Product::where('is_visible', true)
                ->where('name', 'LIKE', "%{$query}%")
                ->take(5)
                ->pluck('name')
                ->toArray();

            // Get collection suggestions
            $collectionSuggestions = Collection::where('is_visible', true)
                ->where('name', 'LIKE', "%{$query}%")
                ->take(3)
                ->pluck('name')
                ->toArray();

            $suggestions = array_merge($productSuggestions, $collectionSuggestions);
        }

        return response()->json($suggestions);
    }
}
