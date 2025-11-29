<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactMessage;

class PageController extends Controller
{
    /**
     * Show the about page.
     */
    public function about()
    {
        return view('pages.about');
    }

    /**
     * Show the contact page.
     */
    public function contact()
    {
        return view('pages.contact');
    }

    /**
     * Customer Services
     */
    public function customerServices()
    {
        return view('pages.customer-services');
    }

    /**
     * FAQs
     */
    public function faqs()
    {
        return view('pages.faqs');
    }

    /**
     * Track Order
     */
    public function trackOrder()
    {
        return view('pages.track-order');
    }

    /**
     * Request Return
     */
    public function requestReturn()
    {
        return view('pages.request-return');
    }

    /**
     * The Legacy
     */
    public function legacy()
    {
        return view('pages.legacy');
    }

    /**
     * Legal Area
     */
    public function legal()
    {
        return view('pages.legal');
    }

    /**
     * Privacy & Cookies
     */
    public function privacy()
    {
        return view('pages.privacy');
    }

    /**
     * Handle contact form submission.
     */
    public function contactSubmit(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        try {
            // You can send email here if needed
            // Mail::to(config('mail.contact_email', 'contact@example.com'))
            //     ->send(new ContactMessage($validated));

            return back()->with('success', 'Thank you for your message! We\'ll get back to you soon.');
        } catch (\Exception $e) {
            return back()->with('error', 'Sorry, there was an issue sending your message. Please try again.');
        }
    }
}
