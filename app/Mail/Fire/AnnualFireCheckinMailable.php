<?php

namespace App\Mail\Fire;

use App\Models\Event;
use App\Mail\BaseMailable;
use Illuminate\Support\Facades\URL;
use App\Traits\FormatsEventHostNames;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use App\Traits\BurnExtinguishFireActions;
use Illuminate\Contracts\Queue\ShouldQueue;

class AnnualFireCheckinMailable extends BaseMailable implements ShouldQueue
{
    use BurnExtinguishFireActions, FormatsEventHostNames, SerializesModels;

    public string $hostNames;

    public function __construct(public Event $event)
    {
        $this->extinguishLink = URL::signedRoute('fire.extinguish', ['event' => $this->event]);
        $this->burnLink = URL::signedRoute('fire.burn', ['event' => $this->event]);
        $this->hostNames = $this->getAllHostNames();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Is your fire still burning? {$this->event->emailName()}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.fire.annual-fire-checkin',
            with: [
                'event' => $this->event,
                'hostNames' => $this->hostNames,
                'burnLink' => $this->burnLink,
                'extinguishLink' => $this->extinguishLink,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
