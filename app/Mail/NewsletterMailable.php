<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewsletterMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $subjectText;
    public $bodyHtml;
    public $subscriber;

    public function __construct(string $subjectText, string $bodyHtml, $subscriber = null)
    {
        $this->subjectText = $subjectText;
        $this->bodyHtml = $bodyHtml;
        $this->subscriber = $subscriber;
        $this->subject($subjectText);
    }

    public function build()
    {
        return $this->view('emails.newsletter')
            ->with(['bodyHtml' => $this->bodyHtml, 'subscriber' => $this->subscriber])
            ->subject($this->subjectText);
    }
}
