<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    /**
     * Display a listing of all collections
     */
    public function index()
    {
        $collections = Collection::visible()
            ->released()
            ->with(['visibleProducts' => function ($query) {
                $query->take(4)->with('images');
            }])
            ->orderBy('release_date', 'desc')
            ->paginate(12);

        return view('collections.index', compact('collections'));
    }

    /**
     * Display the specified collection
     */
    public function show(Collection $collection, Request $request)
    {
        // Check if collection is visible and released
        if (!$collection->visible || $collection->release_date > now()) {
            abort(404);
        }

        // Build products query with sorting
        $productsQuery = $collection->visibleProducts()
            ->with(['images' => function($query) {
                $query->orderBy('order')->limit(1);
            }]);

        // Apply sorting
        switch ($request->get('sort')) {
            case 'name':
                $productsQuery->orderBy('title');
                break;
            case 'price_low':
                $productsQuery->orderBy('price', 'asc');
                break;
            case 'price_high':
                $productsQuery->orderBy('price', 'desc');
                break;
            case 'newest':
                $productsQuery->orderBy('created_at', 'desc');
                break;
            default:
                $productsQuery->orderBy('created_at', 'desc');
                break;
        }

        $products = $productsQuery->paginate(20);

        return view('collections.show', compact('collection', 'products'));
    }
}
