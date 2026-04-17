<?php

namespace App\Http\Controllers\API;

use Statamic\Entries\Entry;
use App\DataTypeObjects\Blueprint;
use App\Builders\EntryQueryBuilder;
use App\Http\Controllers\Controller;
use App\Services\Subsplash\SubsplashApiClient;
use App\DataTypeObjects\API\V1\Content\ExperienceResponse;

class ExperienceDataController extends Controller
{
    public function __construct(
        private SubsplashApiClient $apiClient,
    ) {}

    private function getBySlug($slug)
    {
        $experience = EntryQueryBuilder::experiences()
            ->bySlug($slug)
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
        $experienceResponse->related_event_type = collect(data_get($experienceData, 'related_event_type'))->first();

        $sessions = [];
        $relatedMedia = $this->apiClient->getRelatedMediaItems($experience->subsplash_series_id);
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

    public function all()
    {
        $experiences = EntryQueryBuilder::experiences()
            ->get();

        $output = $experiences->map(fn ($experience) => $this->getBySlug($experience->slug)->toArray());

        return response()->json($output);
    }
}
