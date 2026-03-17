<?php

namespace App\Http\Controllers;

use App\Models\EventType;
use App\Jobs\LogActivityJob;
use Illuminate\Http\Request;

class DrupalUrlController extends Controller
{
    public const LOG_NAME = 'redirects';

    private function logRedirect(string $path, ?object $user): void
    {
        LogActivityJob::dispatch(
            logName: self::LOG_NAME,
            description: "Redirected legacy Drupal URL to Laravel URL: {$path}",
            causedBy: $user,
        );
    }

    public function index(Request $request)
    {
        $this->logRedirect('drupal', $request->user());

        return to_route('home');
    }

    public function courses(Request $request, string $course)
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

    /**
     * This controller is used to redirect legacy Drupal URLs to the new Laravel URLs.
     */
    public function any(Request $request, string $any)
    {
        $eventTypes = [
            'captivating-small-group' => 'captivating/small-group',
            'wild-heart-small-group' => 'wildatheart/small-group',
            'becoming-king-group' => 'allies/becoming-king-group',
            'becoming-king-retreat/' => '',
        ];

        foreach ($eventTypes as $key => $value) {
            if (str_contains($any, $key)) {
                $any = str_replace($key, $eventTypes[$key], $any);
            }
        }

        // Handle edge case where all urls in db end in 0 for some reason.
        if (str_contains($any, 'becoming-king-group')) {
            $any .= '-0';
        }

        $this->logRedirect("drupal/{$any}", $request->user());

        $event = \App\Models\Event::whereLegacyDrupalUrl('/' . $any)->first();

        if (! $event) {
            return to_route('home');
        }

        return to_route('view.event', $event);
    }
}
