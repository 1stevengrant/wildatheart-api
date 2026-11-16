<?php

namespace App\Jobs\Fire;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\EventAutoUnpublishedMail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class UnpublishSingleUnconfirmedFireJob implements ShouldBeUnique, ShouldQueue
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

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $unpublished = DB::transaction(function (): bool {
            $fire = Event::query()->lockForUpdate()->findOrFail($this->event->getKey());

            if (! $fire->isAwaitingAutoUnpublish()) {
                return false;
            }

            $fire->update([
                'approved_at' => null,
                'auto_unpublished' => true,
                'auto_unpublished_at' => now(),
            ]);

            return true;
        });

        if (! $unpublished) {
            return;
        }

        $this->event->refresh();

        activity('fire')
            ->performedOn($this->event)
            ->log('Auto-unpublished due to unconfirmed check-in');

        // Send to primary host with managers CC'd
        $mail = Mail::to($this->event->user->email);

        if ($this->event->managers->isNotEmpty()) {
            $mail->cc($this->event->managers->pluck('email'));
        }

        $mail->send(new EventAutoUnpublishedMail($this->event));
    }
}
