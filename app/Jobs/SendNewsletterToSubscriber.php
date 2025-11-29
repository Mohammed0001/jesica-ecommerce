<?php

namespace App\Jobs;

use App\Mail\NewsletterMailable;
use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendNewsletterToSubscriber implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $subscriberId;
    public string $subject;
    public string $bodyHtml;

    public function __construct(int $subscriberId, string $subject, string $bodyHtml)
    {
        $this->subscriberId = $subscriberId;
        $this->subject = $subject;
        $this->bodyHtml = $bodyHtml;
    }

    public function handle(): void
    {
        $sub = NewsletterSubscriber::find($this->subscriberId);

        $hasNewsletterChannel = is_array(config('logging.channels')) && array_key_exists('newsletter', config('logging.channels'));

        if (! $sub) {
            if ($hasNewsletterChannel) {
                Log::channel('newsletter')->warning('Subscriber not found for newsletter job', ['subscriber_id' => $this->subscriberId]);
            } else {
                Log::warning('Subscriber not found for newsletter job', ['subscriber_id' => $this->subscriberId]);
            }

            return;
        }

        if (! $sub->is_subscribed) {
            if ($hasNewsletterChannel) {
                Log::channel('newsletter')->info('Skipping unsubscribed subscriber', ['subscriber_id' => $sub->id, 'email' => $sub->email]);
            } else {
                Log::info('Skipping unsubscribed subscriber', ['subscriber_id' => $sub->id, 'email' => $sub->email]);
            }

            return; // nothing to do
        }

        if ($hasNewsletterChannel) {
            Log::channel('newsletter')->info('SendNewsletterToSubscriber job started', ['subscriber_id' => $sub->id, 'email' => $sub->email]);
        } else {
            Log::info('SendNewsletterToSubscriber job started', ['subscriber_id' => $sub->id, 'email' => $sub->email]);
        }

        try {
            // Build the mailable with freshly loaded model instance to avoid serialization edge cases
            $mailable = new NewsletterMailable($this->subject, $this->bodyHtml, $sub);
            Mail::to($sub->email)->send($mailable);

            if ($hasNewsletterChannel) {
                Log::channel('newsletter')->info('Newsletter sent successfully', ['subscriber_id' => $sub->id, 'email' => $sub->email]);
            } else {
                Log::info('Newsletter sent successfully', ['subscriber_id' => $sub->id, 'email' => $sub->email]);
            }
        } catch (\Throwable $e) {
            // Log and swallow the exception so a single failing recipient doesn't spam failed_jobs
            if ($hasNewsletterChannel) {
                Log::channel('newsletter')->error('Newsletter send failed for subscriber', [
                    'subscriber_id' => $sub->id,
                    'email' => $sub->email,
                    'error' => $e->getMessage(),
                ]);
            } else {
                Log::error('Newsletter send failed for subscriber', [
                    'subscriber_id' => $sub->id,
                    'email' => $sub->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
