<?php

namespace App\Mail\User;

use App\Models\User;
use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\URL;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use App\Traits\InteractsWithEmailTemplates;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProfileUpdateReminder extends Mailable implements ShouldQueue
{
    use InteractsWithEmailTemplates, Queueable, SerializesModels;

    public EmailTemplate $emailTemplate;

    public string $emailContent;

    public string $updateLink;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $user
    ) {
        $this->emailTemplate = $this->getEmailTemplate('profile-update-reminder');
        $this->updateLink = URL::signedRoute('profile.update.names', ['user' => $user->uuid]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->emailTemplate->subject;
        $subject = str_replace('[user_email]', $this->user->email, $subject);

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $content = $this->emailTemplate->body;
        $content = str_replace('[user_email]', $this->user->email, $content);
        $content = str_replace('[update_link]', $this->updateLink, $content);
        $this->emailContent = $content;

        return new Content(
            markdown: 'emails.user.profile-update-reminder',
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
