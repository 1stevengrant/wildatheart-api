<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Event;
use App\Data\PageData;
use App\Jobs\LogActivityJob;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use App\Mail\Fire\ConfirmFireUnpublishedMailable;

class HandleMonthlyHostFireEmailController extends Controller
{
    public function update(Event $event)
    {
        $event->update([
            'approved_at' => now(),
        ]);

        LogActivityJob::dispatch(
            logName: 'fire',
            description: 'Fire published',
            properties: [
                'event_id' => $event->id,
                'user_id' => $event->user->id,
            ],
            causedBy: $event->user,
            performedOn: $event,
        );

        return inertia('FireBurningPage', [
            'pageContent' => PageData::from(Page::bySlug('fire-burning-page')),
        ]);
    }

    public function show(Event $event)
    {
        LogActivityJob::dispatch(
            logName: 'fire',
            description: 'Fire unpublished page viewed',
            properties: [
                'event_id' => $event->id,
                'user_id' => $event->user->id,
            ],
            causedBy: $event->user,
            performedOn: $event,
        );

        return inertia('FireExtinguishedPage', [
            'event' => $event,
            'destroyUrl' => URL::signedRoute('fire.extinguish.destroy', ['event' => $event]),
            'pageContent' => PageData::from(Page::bySlug('fire-extinguished-page')),
        ]);
    }

    public function destroy(Event $event)
    {
        $event->unpublish();

        LogActivityJob::dispatch(
            logName: 'fire',
            description: 'Fire unpublished',
            properties: [
                'event_id' => $event->id,
                'user_id' => $event->user->id,
            ],
            causedBy: $event->user,
            performedOn: $event,
        );

        Mail::to($event->user->email)
            ->send(new ConfirmFireUnpublishedMailable($event));

        return inertia('HomePage')
            ->with(['notifications' => [
                'type' => 'success',
                'message' => 'Your Fire has been extinguished.',
            ]]);
    }
}
