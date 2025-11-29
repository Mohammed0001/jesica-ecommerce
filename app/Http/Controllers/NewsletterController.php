<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'name' => 'nullable|string|max:255',
        ]);

        try {
            NewsletterSubscriber::subscribe($request->email, $request->name);
        } catch (\Exception $e) {
            Log::error('Newsletter subscribe failed: ' . $e->getMessage());
            return Redirect::back()->with('error', 'Unable to subscribe at this time.');
        }

        return Redirect::back()->with('success', 'Subscribed to newsletter');
    }

    public function unsubscribe($id, $token)
    {
        $sub = NewsletterSubscriber::find($id);
        if (!$sub || $sub->unsubscribe_token !== $token) {
            abort(404);
        }

        $sub->unsubscribe();

        return view('newsletter.unsubscribed');
    }

    /** Admin: show send newsletter form */
    public function showSendForm()
    {
        return view('admin.newsletter_send');
    }

    /** Admin: send newsletter to all subscribers (queued) */
    public function send(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $subs = NewsletterSubscriber::where('is_subscribed', true)->get();

        foreach ($subs as $sub) {
            try {
                \Illuminate\Support\Facades\Mail::to($sub->email)->queue(new \App\Mail\NewsletterMailable($request->subject, $request->body, $sub));
            } catch (\Exception $e) {
                Log::error('Failed to queue newsletter to ' . $sub->email . ': ' . $e->getMessage());
            }
        }

        return Redirect::back()->with('success', 'Newsletter queued to subscribers');
    }
}
