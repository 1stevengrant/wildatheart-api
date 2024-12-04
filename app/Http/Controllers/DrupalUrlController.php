<?php

namespace App\Http\Controllers;

use App\Jobs\LogActivityJob;
use App\Models\EventType;
use Illuminate\Http\Request;

class DrupalUrlController extends Controller
{
    public const LOG_NAME = 'redirects';

    public function index(Request $request)
    {
        dispatch(new LogActivityJob(
            logName: self::LOG_NAME,
            description: 'Redirected legacy Drupal URL to Laravel URL: drupal',
            causedBy: $request->user(),
        ));

        return redirect()->route('home');
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

        if (isset($eventTypes[$course])) {
            $eventType = EventType::whereName($eventTypes[$course])->first();

            dispatch(new LogActivityJob(
                logName: self::LOG_NAME,
                description: 'Redirected legacy Drupal URL to Laravel URL: drupal/courses/'.$course,
                causedBy: $request->user(),
            ));

            return redirect()->route('host.eventType.index', $eventType);
        }

        dispatch(new LogActivityJob(
            logName: self::LOG_NAME,
            description: 'Redirected legacy Drupal URL to Laravel URL: drupal/courses/'.$course,
            causedBy: $request->user(),
        ));

        return redirect()->route('home');
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

        $event = \App\Models\Event::whereLegacyDrupalUrl('/'.$any)->first();
        if (! $event) {
            dispatch(new LogActivityJob(
                logName: self::LOG_NAME,
                description: 'Redirected legacy Drupal URL to Laravel URL: drupal/'.$any,
                causedBy: $request->user(),
            ));

            return redirect()->route('home');
        }

        dispatch(new LogActivityJob(
            logName: self::LOG_NAME,
            description: 'Redirected legacy Drupal URL to Laravel URL: drupal/'.$any,
            causedBy: $request->user(),
        ));

        return redirect()->route('view.event', $event);
    }
}
