<?php

namespace App\Mail\Event;

use App\Models\User;
use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use App\Traits\InteractsWithEmailTemplates;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendEmailVerificationEmail extends Mailable implements ShouldQueue
{
    use InteractsWithEmailTemplates, Queueable, SerializesModels;

    public EmailTemplate $emailTemplate;

    public string $emailContent;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public string $verificationToken,
        public User $user,
    ) {
        $this->emailTemplate = $this->getEmailTemplate('send-verification-email');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailTemplate->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $route = route('verify.email', $this->verificationToken);
        $content = $this->emailTemplate->body;
        $content = str_replace('[call_to_action_link]', "[Verify Email]({$route})", $content);
        $this->emailContent = $content;

        return new Content(
            markdown: 'emails.users.send-email-verification-email',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
