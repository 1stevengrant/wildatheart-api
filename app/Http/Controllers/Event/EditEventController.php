<?php

namespace App\Http\Controllers\Event;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\EventEditPageService;

class EditEventController extends Controller
{
    public function __invoke(Request $request, Event $event, EventEditPageService $service)
    {
        $this->authorize('update', $event);

        return inertia('Profile/EditEventPage', $service->buildPageData($event));
    }
}
