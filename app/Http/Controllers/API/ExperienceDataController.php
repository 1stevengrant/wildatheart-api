<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Statamic\Entries\Entry;

class ExperienceDataController extends Controller
{
    public function show($slug)
    {

        $experience = Entry::query()
            ->where('collection', 'experiences')
            ->where('slug', $slug)
            ->firstOrFail();

        $sessions = [];
        foreach ($experience->sessions as $index => $session) {

            $experienceSession = Entry::query()
                ->find($session);
            $sessions[$index] = $experienceSession;
        }

        $experience->sessionData = $sessions;

        return response()->json($experience);
    }
}
