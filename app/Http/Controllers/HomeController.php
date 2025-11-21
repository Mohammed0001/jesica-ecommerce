<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Display the homepage with featured collections and new arrivals
     */
    public function index()
    {
        // Get featured collections (visible and released)
        $featuredCollections = Collection::visible()
            ->released()
            ->orderBy('release_date', 'desc')
            ->take(3)
            ->with(['products' => function ($query) {
                $query->visible()->take(4);
            }])
            ->get();

        // Get featured products (latest products)
        $featuredProducts = Product::visible()
            ->with(['collection', 'images'])
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        return view('home', compact('featuredCollections', 'featuredProducts'));
    }

    /**
     * Display the about page
     */
    public function about()
    {
        return view('pages.about');
    }

    /**
     * Display the contact page
     */
    public function contact()
    {
        return view('pages.contact');
    }

    /**
     * Handle contact form submission
     */
    public function contactSubmit(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        // TODO: Send email or store in database
        // For now, just flash a success message

        return back()->with('success', __('Thank you for your message. We will get back to you soon.'));
    }
}
