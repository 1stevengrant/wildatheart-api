<?php

namespace App\Http\Controllers\Drupal;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Drupal\Concerns\LogsDrupalRedirect;

class DrupalFallbackRedirectController extends Controller
{
    use LogsDrupalRedirect;

    public function __invoke(Request $request, string $any): RedirectResponse
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

        if (str_contains($any, 'becoming-king-group')) {
            $any .= '-0';
        }

        $this->logRedirect("drupal/{$any}", $request->user());

        $event = Event::whereLegacyDrupalUrl('/' . $any)->first();

        if (! $event) {
            return to_route('home');
        }

        return to_route('view.event', $event);
    }
}
