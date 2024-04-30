<?php

namespace App\Http\Controllers;

use App\Mail\Fire\ConfirmFireUnpublishedMailable;
use App\Models\Event;
use App\Models\Page;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class HandleMonthlyHostFireEmailController extends Controller
{
    public function update(Event $event)
    {
        $event->update([
            'approved_at' => now(),
        ]);

        return inertia('FireBurningPage', [
            'pageContent' => Page::bySlug('fire-burning-page'),
        ]);
    }

    public function show(Event $event)
    {
        return inertia('FireExtinguishedPage', [
            'event' => $event,
            'destroyUrl' => URL::signedRoute('fire.extinguish.destroy', ['event' => $event]),
            'pageContent' => Page::bySlug('fire-extinguished-page'),
        ]);
    }

    public function destroy(Event $event)
    {
        $event->unpublish();

        Mail::to($event->user->email)
            ->send(new ConfirmFireUnpublishedMailable($event));

        return inertia('Home')
            ->with(['notifications' => [
                'type' => 'success',
                'message' => 'Your Fire has been extinguished.',
            ]]);
    }
}
