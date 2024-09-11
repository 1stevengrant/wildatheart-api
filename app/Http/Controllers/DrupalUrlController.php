<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DrupalUrlController extends Controller
{

    public function index(string $any)
    {
        return redirect()->route('home');
    }


    /**
     * This controller is used to redirect legacy Drupal URLs to the new Laravel URLs.
     */
    public function redirect(Request $request, string $type, string $legacyDrupalUrl)
    {
        $event = \App\Models\Event::whereLegacyDrupalUrl('/'.$legacyDrupalUrl)->first();

        if (! $event) {
            return redirect()->route('home');
        }

        activity()
            ->causedBy($request->user())
            ->performedOn($event)
            ->log('Redirected legacy Drupal URL to Laravel URL');

        return redirect()->route('view.event', $event);
    }
}
