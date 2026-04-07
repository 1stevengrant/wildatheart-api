<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Event;
use App\Jobs\LogActivityJob;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use App\Mail\Fire\ConfirmFireUnpublishedMailable;

class HandleMonthlyHostFireEmailController extends Controller
{
    public function update(Event $event)
    {
        $event->update([
            'last_checkin_confirmed_at' => now(),
            'last_checkin_email_sent_at' => now(),
            'last_checkin_reminder_sent_at' => null,
            'auto_unpublished' => false,
            'auto_unpublished_at' => null,
            'approved_at' => now(),
        ]);

        LogActivityJob::dispatch(
            description: 'Fire published',
            causedBy: $event->user,
            performedOn: $event,
        );

        return inertia('FireBurningPage', [
            'pageContent' => Page::bySlug('fire-burning-page'),
        ]);
    }

    public function show(Event $event)
    {
        LogActivityJob::dispatch(
            description: 'Fire unpublished page viewed',
            causedBy: $event->user,
            performedOn: $event,
        );

        return inertia('FireExtinguishedPage', [
            'event' => $event,
            'destroyUrl' => URL::signedRoute('fire.extinguish.destroy', ['event' => $event]),
            'pageContent' => Page::bySlug('fire-extinguished-page'),
        ]);
    }

    public function destroy(Event $event)
    {
        $event->update([
            'approved_at' => null,
            'auto_unpublished' => false,
        ]);

        LogActivityJob::dispatch(
            description: 'Fire unpublished',
            causedBy: $event->user,
            performedOn: $event,
        );

        $recipients = collect([$event->user])->merge($event->managers);

        $recipients->each(function ($recipient) use ($event) {
            Mail::to($recipient->email)->send(new ConfirmFireUnpublishedMailable($event));
        });

        return to_route('home')
            ->with(['notifications' => [
                'type' => 'success',
                'message' => 'Your Fire has been extinguished.',
            ]]);
    }
}
