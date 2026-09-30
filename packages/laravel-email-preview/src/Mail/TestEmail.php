<?php

namespace Ghijk\EmailPreview\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Mail\Mailables\Envelope;

class TestEmail extends Mailable
{
    public function __construct(public string $token, public string $mailerName) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Test email [{$this->token}]",
        );
    }

    public function headers(): Headers
    {
        return new Headers(
            text: ['X-Mailer' => self::class . '::' . $this->token],
        );
    }

    public function content(): Content
    {
        $sentAt = now()->toDateTimeString();

        return new Content(
            htmlString: "<p>This is a test email sent from the captured emails page.</p><p>Mailer: <strong>{$this->mailerName}</strong><br>Sent at: {$sentAt}<br>Token: {$this->token}</p>",
        );
    }
}
