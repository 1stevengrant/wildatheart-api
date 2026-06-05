<?php

use App\Mail\BaseMailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

it('defaults queued mailables to the mail queue with retries and exponential backoff', function (): void {
    $mailable = new class extends BaseMailable
    {
        public function envelope(): Envelope
        {
            return new Envelope(subject: 'test');
        }

        public function content(): Content
        {
            return new Content(htmlString: '<p>test</p>');
        }
    };

    expect($mailable->queue)->toBe('mail');
    expect($mailable->tries)->toBe(3);
    expect($mailable->backoff())->toBe([60, 300, 900]);
});
