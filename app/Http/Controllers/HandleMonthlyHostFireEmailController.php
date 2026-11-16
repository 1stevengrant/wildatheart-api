<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Event;
use App\Jobs\LogActivityJob;
use Illuminate\Support\Facades\URL;
use App\Actions\Event\ExtinguishFire;
use App\Actions\Event\ConfirmFireIsBurning;

class HandleMonthlyHostFireEmailController extends Controller
{
    public function update(Event $event)
    {
        ConfirmFireIsBurning::run($event, $event->user);

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
        ExtinguishFire::run($event, $event->user);

        return to_route('home')
            ->with(['notifications' => [
                'type' => 'success',
                'message' => 'Your Fire has been extinguished.',
            ]]);
    }
}
