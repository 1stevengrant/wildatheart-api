<?php

use Statamic\Facades\Entry;
use Statamic\Facades\Collection;
use App\Services\EntryBuilderService;
use App\Services\EventResponseBuilder;

beforeEach(function () {
    foreach (['events', 'experiences'] as $handle) {
        if (! Collection::handleExists($handle)) {
            Collection::make($handle)->save();
        }
    }
});

function landingPage(string $blueprintHandle): Statamic\Entries\Entry
{
    $data = ['title' => 'Landing Page'];

    if ($blueprintHandle === 'video_study') {
        // A video_study landing page is a pointer: the real content, and the
        // logo, live on the linked experiences entry.
        $experience = Entry::make()
            ->collection('experiences')
            ->blueprint('experience')
            ->slug('experience-' . uniqid())
            ->published(true);

        $experience->data(['title' => 'Linked Experience']);
        $experience->save();

        $data['related_content'] = $experience->id();
    }

    $entry = Entry::make()
        ->collection('events')
        ->blueprint($blueprintHandle)
        ->slug('landing-page-' . $blueprintHandle . '-' . uniqid())
        ->published(true);

    $entry->data($data);
    $entry->save();

    return $entry;
}

function builderReturning(array $response): EventResponseBuilder
{
    $entryBuilder = Mockery::mock(EntryBuilderService::class);
    $entryBuilder->shouldReceive('buildResponseFromEntry')->andReturn($response);

    return new EventResponseBuilder($entryBuilder);
}

it('exposes an experiences logo under event_logo', function () {
    $logo = ['id' => 's3::captivating-experience-white.png', 'api_url' => 'https://example.test/logo.png'];

    $response = builderReturning(['title' => 'Captivating Experience', 'experience_logo' => $logo])
        ->build(landingPage('video_study'));

    expect($response['event_logo'])->toBe($logo);
});

it('leaves the experiences logo in place alongside the normalised key', function () {
    $logo = ['id' => 's3::captivating-experience-white.png'];

    $response = builderReturning(['experience_logo' => $logo])
        ->build(landingPage('video_study'));

    expect($response['experience_logo'])->toBe($logo);
});

it('prefers an explicit event_logo over the experiences logo', function () {
    $eventLogo = ['id' => 's3::event.png'];
    $experienceLogo = ['id' => 's3::experience.png'];

    $response = builderReturning([
        'event_logo' => $eventLogo,
        'experience_logo' => $experienceLogo,
    ])->build(landingPage('video_study'));

    expect($response['event_logo'])->toBe($eventLogo);
});

it('leaves an existing event_logo untouched when there is no experiences logo', function () {
    $eventLogo = ['id' => 's3::becoming-a-king.png'];

    $response = builderReturning(['event_logo' => $eventLogo])
        ->build(landingPage('multiday_retreat'));

    expect($response['event_logo'])->toBe($eventLogo);
});

it('reports a null event_logo when neither blueprint supplies one', function () {
    $response = builderReturning(['title' => 'No Logo Here'])
        ->build(landingPage('multiday_retreat'));

    expect($response)->toHaveKey('event_logo')
        ->and($response['event_logo'])->toBeNull();
});

it('still normalises the video url fields', function () {
    $response = builderReturning([
        'background_video_url' => ['url' => 'https://example.test/background.mp4'],
        'featured_video_url' => ['url' => 'https://example.test/featured.mp4'],
    ])->build(landingPage('multiday_retreat'));

    expect($response['background_video_url'])->toBe('https://example.test/background.mp4')
        ->and($response['featured_video_url'])->toBe('https://example.test/featured.mp4');
});
