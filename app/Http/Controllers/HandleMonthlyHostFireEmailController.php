<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Event;
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

        activity('fire')
            ->performedOn($event)
            ->causedBy($event->user)
            ->log('Fire published');

        return inertia('FireBurningPage', [
            'pageContent' => Page::bySlug('fire-burning-page'),
        ]);
    }

    public function show(Event $event)
    {
        activity('fire')
            ->performedOn($event)
            ->causedBy($event->user)
            ->log('Fire unpublished page viewed');

        return inertia('FireExtinguishedPage', [
            'event' => $event,
            'destroyUrl' => URL::signedRoute('fire.extinguish.destroy', ['event' => $event]),
            'pageContent' => Page::bySlug('fire-extinguished-page'),
        ]);
    }

    public function destroy(Event $event)
    {
        $event->unpublish();

        activity('fire')
            ->performedOn($event)
            ->causedBy($event->user)
            ->log('Fire unpublished');

        Mail::to($event->user->email)
            ->send(new ConfirmFireUnpublishedMailable($event));

        return inertia('Home')
            ->with(['notifications' => [
                'type' => 'success',
                'message' => 'Your Fire has been extinguished.',
            ]]);
    }
}
