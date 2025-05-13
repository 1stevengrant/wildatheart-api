<?php

namespace App\Http\Controllers\API;

use Statamic\Entries\Entry;
use App\DataTypeObjects\Blueprint;
use App\Services\SubsplashService;
use App\Http\Controllers\Controller;
use App\DataTypeObjects\API\V1\Content\ExperienceResponse;

class ExperienceDataController extends Controller
{
    private function getBySlug($slug)
    {
        $experience = Entry::query()
            ->where('collection', 'experiences')
            ->where('slug', $slug)
            ->firstOrFail();

        $experienceData = $experience->toArray();
        $experienceResponse = new ExperienceResponse;
        $experienceResponse->id = $experienceData['id'];
        $experienceResponse->title = $experienceData['title'];
        $experienceResponse->slug = $experienceData['slug'];
        $experienceResponse->uri = $experienceData['uri'];
        $experienceResponse->url = $experienceData['url'];
        $experienceResponse->blueprint = Blueprint::from($experienceData['blueprint']);
        $experienceResponse->locale = $experienceData['locale'];
        $experienceResponse->private = $experienceData['private'];
        $experienceResponse->published = $experienceData['published'];
        $experienceResponse->status = $experienceData['status'];
        $experienceResponse->blocks = $experienceData['blocks'];
        $experienceResponse->height = data_get($experienceData, 'height.value');
        $experienceResponse->images = $experienceData['images'];
        $experienceResponse->position = data_get($experienceData, 'position.value');
        $experienceResponse->sessions = $experienceData['sessions'];
        $experienceResponse->subtitle = $experienceData['subtitle'];
        $experienceResponse->hero_logo = data_get($experienceData, 'hero_logo.value');
        $experienceResponse->hero_title = data_get($experienceData, 'hero_title');
        $experienceResponse->hero_subtitle = data_get($experienceData, 'hero_subtitle');
        $experienceResponse->hero_variant = data_get($experienceData, 'hero_variant.value');
        $experienceResponse->testimonials = $experienceData['testimonials'];
        $experienceResponse->background_video_url = data_get($experienceData, 'background_video_url.url');
        $experienceResponse->background_video_fallback_image = data_get($experienceData, 'background_video_fallback_image.url');
        $experienceResponse->featured_video_url = data_get($experienceData, 'featured_video_url.url');
        $experienceResponse->featured_video_fallback_image = data_get($experienceData, 'featured_video_fallback_image');
        $experienceResponse->experience_logo = data_get($experienceData, 'experience_logo');

        $sessions = [];
        $subsplash = new SubsplashService;
        $relatedMedia = $subsplash->getRelatedMediaItems($experience->subsplash_series_id);
        $relatedMediaMap = [];

        foreach ($relatedMedia as $mediaItem) {
            $relatedMediaMap[$mediaItem['id']] = $mediaItem;
        }
        foreach ($experience->sessions as $index => $session) {

            $experienceSession = Entry::query()
                ->find($session->id);
            $sessionSlug = 'session-' . ($index + 1);
            $sessions[$index] = [
                'sessionNumber' => $index + 1,
                'sessionSlug' => $sessionSlug,
                'sessionUri' => $experience->uri . '/' . $sessionSlug,
                ...$experienceSession->toArray(),
                ...$relatedMediaMap[$experienceSession->subsplash_media_item_id],
            ];
        }

        $experienceResponse->sessionData = $sessions;

        return $experienceResponse;
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
