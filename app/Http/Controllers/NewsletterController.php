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

        // Log start of newsletter send (use newsletter channel if available)
        $hasNewsletterChannel = is_array(config('logging.channels')) && array_key_exists('newsletter', config('logging.channels'));
        if ($hasNewsletterChannel) {
            \Illuminate\Support\Facades\Log::channel('newsletter')->info('Starting newsletter dispatch', [
                'subject' => $request->subject,
                'subscribers' => $subs->count(),
            ]);
        } else {
            \Illuminate\Support\Facades\Log::info('Starting newsletter dispatch', [
                'subject' => $request->subject,
                'subscribers' => $subs->count(),
            ]);
        }

        foreach ($subs as $sub) {
            try {
                // Dispatch a lightweight job which loads the subscriber inside the job and sends the mail.
                \App\Jobs\SendNewsletterToSubscriber::dispatch($sub->id, $request->subject, $request->body);

                if ($hasNewsletterChannel) {
                    \Illuminate\Support\Facades\Log::channel('newsletter')->info('Dispatched newsletter job', [
                        'subscriber_id' => $sub->id,
                        'email' => $sub->email,
                    ]);
                } else {
                    \Illuminate\Support\Facades\Log::info('Dispatched newsletter job', [
                        'subscriber_id' => $sub->id,
                        'email' => $sub->email,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to dispatch newsletter job for ' . $sub->email . ': ' . $e->getMessage());
                if ($hasNewsletterChannel) {
                    \Illuminate\Support\Facades\Log::channel('newsletter')->error('Failed to dispatch newsletter job', [
                        'subscriber_id' => $sub->id ?? null,
                        'email' => $sub->email ?? null,
                        'error' => $e->getMessage(),
                    ]);
                } else {
                    \Illuminate\Support\Facades\Log::error('Failed to dispatch newsletter job', [
                        'subscriber_id' => $sub->id ?? null,
                        'email' => $sub->email ?? null,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        if ($hasNewsletterChannel) {
            \Illuminate\Support\Facades\Log::channel('newsletter')->info('Completed dispatching newsletter jobs', [
                'subject' => $request->subject,
                'subscribers' => $subs->count(),
            ]);
        } else {
            \Illuminate\Support\Facades\Log::info('Completed dispatching newsletter jobs', [
                'subject' => $request->subject,
                'subscribers' => $subs->count(),
            ]);
        }

        return Redirect::back()->with('success', 'Newsletter queued to subscribers');
    }
}
