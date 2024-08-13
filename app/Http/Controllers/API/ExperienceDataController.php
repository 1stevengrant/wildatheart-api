<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\SubsplashService;
use Statamic\Entries\Entry;

class ExperienceDataController extends Controller
{
    private function getBySlug($slug)
    {
        $experience = Entry::query()
            ->where('collection', 'experiences')
            ->where('slug', $slug)
            ->firstOrFail();

        $sessions = [];
        $subsplash = new SubsplashService;
        $relatedMedia = $subsplash->getRelatedMediaItems($experience->subsplash_series_id);
        $relatedMediaMap = [];

        foreach ($relatedMedia as $mediaItem) {
            $relatedMediaMap[$mediaItem['id']] = $mediaItem;
        }
        foreach ($experience->sessions as $index => $session) {

            $experienceSession = Entry::query()
                ->find($session);
            $sessions[$index] = [
                'sessionNumber' => $index + 1,
                ...$experienceSession->toArray(),
                ...$relatedMediaMap[$experienceSession->subsplash_media_item_id],
            ];
        }

        $experience->sessionData = $sessions;

        dd($experience);

        return $experience;
    }

    public function show($slug)
    {

        $experience = $this->getBySlug($slug);

        return response()->json($experience);
    }

    private function getSessionData($experience)
    {
        $sessions = [];
    }

    public function all()
    {
        $experiences = Entry::query()
            ->where('collection', 'experiences')
            ->get();

        $output = [];
        foreach ($experiences as $experience) {

            $experienceData = $this->getBySlug($experience->slug);
            $output[] = [
                ...$experienceData->toArray(),
            ];
        }

        return response()->json($output);
    }
}
