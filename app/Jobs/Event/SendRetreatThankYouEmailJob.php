<?php

namespace App\Jobs\Event;

use App\Models\Event;
use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Mail\Event\RetreatThankYouMailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class SendRetreatThankYouEmailJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public int $retryAfter = 300;

    public function __construct(public Event $event)
    {
        //
    }

    public function uniqueId(): string
    {
        return $this->event->id;
    }

    public function handle(): void
    {
        if ($this->event->post_event_email_sent_at !== null) {
            return;
        }

        $templateSlug = RetreatThankYouMailable::templateSlugFor($this->event);

        if ($templateSlug === null) {
            return;
        }

        $emailTemplate = EmailTemplate::where('slug', $templateSlug)->firstOrFail();

        if (! $emailTemplate->is_active) {
            return;
        }

        $this->event->update([
            'post_event_email_sent_at' => now(),
        ]);

        activity('event')
            ->performedOn($this->event)
            ->log('Retreat thank-you email sent');

        $mail = Mail::to($this->event->user);

        if ($emailTemplate->cc_co_hosts && $this->event->managers->isNotEmpty()) {
            $mail->cc($this->event->managers->pluck('email'));
        }

        $mail->send(new RetreatThankYouMailable($this->event, $emailTemplate));
    }
}
