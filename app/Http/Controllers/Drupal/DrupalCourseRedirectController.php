<?php

namespace App\Http\Controllers\Drupal;

use App\Models\EventType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Drupal\Concerns\LogsDrupalRedirect;

class DrupalCourseRedirectController extends Controller
{
    use LogsDrupalRedirect;

    public function __invoke(Request $request, string $course): RedirectResponse
    {
        $eventTypes = [
            'basic' => EventType::WAH_RETREAT,
            'core' => EventType::CAPTIVATING_CORE_RETREAT,
            'becomingakingretreat' => EventType::BECOMING_A_KING,
            'wildatheart' => EventType::WAH_EXPERIENCE,
            'captivating' => EventType::CAPTIVATING_EXPERIENCE,
            'becomingaking' => EventType::BECOMING_A_KING_EXPERIENCE,
        ];

        $this->logRedirect("drupal/courses/{$course}", $request->user());

        if (isset($eventTypes[$course])) {
            $eventType = EventType::whereName($eventTypes[$course])->first();

            return to_route('host.eventType.index', $eventType);
        }

        return to_route('home');
    }
}
