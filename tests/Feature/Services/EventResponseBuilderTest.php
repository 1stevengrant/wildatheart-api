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

function landingPage(string $blueprintHandle, bool $linked = true): Statamic\Entries\Entry
{
    $data = ['title' => 'Landing Page'];

    if ($blueprintHandle === 'video_study' && $linked) {
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

it('falls back to the pointer entry when a video_study has no related content', function () {
    $entryBuilder = Mockery::mock(EntryBuilderService::class);
    $entryBuilder->shouldReceive('buildResponseFromEntry')
        ->once()
        ->with(Mockery::type(Statamic\Entries\Entry::class))
        ->andReturn(['title' => 'Unlinked Study']);

    $unlinked = landingPage('video_study', linked: false);

    expect($unlinked->related_content)->toBeNull();

    $response = (new EventResponseBuilder($entryBuilder))->build($unlinked);

    expect($response['title'])->toBe('Unlinked Study')
        ->and($response['event_logo'])->toBeNull();
});

it('builds from the linked entry when a video_study has related content', function () {
    $linked = landingPage('video_study');

    $entryBuilder = Mockery::mock(EntryBuilderService::class);
    $entryBuilder->shouldReceive('buildResponseFromEntry')
        ->once()
        ->with(Mockery::on(fn ($entry) => $entry->id() === $linked->related_content->id()))
        ->andReturn(['title' => 'Linked Experience']);
    $entryBuilder->shouldReceive('buildResponseFromEntry')
        ->once()
        ->with(Mockery::on(fn ($entry) => $entry->id() === $linked->id()))
        ->andReturn(['title' => 'Landing Page', 'topics' => []]);

    $response = (new EventResponseBuilder($entryBuilder))->build($linked);

    expect($response['title'])->toBe('Linked Experience');
});

it('retains augmented topics from a video_study while building landing page fields from related content', function () {
    $linked = landingPage('video_study');
    $parentTopics = [
        ['slug' => 'for-women', 'title' => 'For Women'],
        ['slug' => 'beginner-friendly', 'title' => 'Beginner Friendly'],
    ];
    $experienceLogo = ['id' => 's3::captivating-experience-white.png'];
    $heroTitle = [
        ['text' => 'Restore Your', 'type' => 'text_line'],
        ['text' => 'Feminine Heart', 'type' => 'text_line'],
    ];
    $heroSubtitle = [
        ['type' => 'text', 'text' => '<p>A six-session video series.</p>'],
    ];

    $entryBuilder = Mockery::mock(EntryBuilderService::class);
    $entryBuilder->shouldReceive('buildResponseFromEntry')
        ->once()
        ->with(Mockery::on(fn ($entry) => $entry->id() === $linked->related_content->id()))
        ->andReturn([
            'title' => 'Linked Experience',
            'topics' => [],
            'experience_logo' => $experienceLogo,
            'blocks' => [],
            'hero_title' => $heroTitle,
            'hero_subtitle' => $heroSubtitle,
        ]);
    $entryBuilder->shouldReceive('buildResponseFromEntry')
        ->once()
        ->with(Mockery::on(fn ($entry) => $entry->id() === $linked->id()))
        ->andReturn([
            'title' => 'Landing Page',
            'topics' => $parentTopics,
        ]);

    $response = (new EventResponseBuilder($entryBuilder))->build($linked);

    expect($response['title'])->toBe('Linked Experience')
        ->and($response['topics'])->toBe($parentTopics)
        ->and($response['blocks'])->toBe([
            ['hero_title' => $heroTitle, 'type' => 'hero_title'],
            ...$heroSubtitle,
        ])
        ->and($response['experience_logo'])->toBe($experienceLogo)
        ->and($response['event_logo'])->toBe($experienceLogo);
});

it('preserves non-empty related blocks instead of replacing them with parent presentation content', function () {
    $linked = landingPage('video_study');
    $relatedBlocks = [
        ['type' => 'text', 'text' => '<p>Related presentation</p>'],
    ];

    $entryBuilder = Mockery::mock(EntryBuilderService::class);
    $entryBuilder->shouldReceive('buildResponseFromEntry')
        ->with(Mockery::on(fn ($entry) => $entry->id() === $linked->related_content->id()))
        ->andReturn([
            'blocks' => $relatedBlocks,
            'hero_title' => [['text' => 'Legacy title']],
        ]);
    $entryBuilder->shouldReceive('buildResponseFromEntry')
        ->with(Mockery::on(fn ($entry) => $entry->id() === $linked->id()))
        ->andReturn([
            'blocks' => [['type' => 'text', 'text' => '<p>Parent presentation</p>']],
            'topics' => [],
        ]);

    $response = (new EventResponseBuilder($entryBuilder))->build($linked);

    expect($response['blocks'])->toBe($relatedBlocks);
});

it('uses parent presentation blocks when related blocks are empty', function () {
    $linked = landingPage('video_study');
    $parentBlocks = [
        ['type' => 'text', 'text' => '<p>Parent presentation</p>'],
    ];

    $entryBuilder = Mockery::mock(EntryBuilderService::class);
    $entryBuilder->shouldReceive('buildResponseFromEntry')
        ->with(Mockery::on(fn ($entry) => $entry->id() === $linked->related_content->id()))
        ->andReturn([
            'blocks' => [],
            'hero_title' => [['text' => 'Legacy title']],
        ]);
    $entryBuilder->shouldReceive('buildResponseFromEntry')
        ->with(Mockery::on(fn ($entry) => $entry->id() === $linked->id()))
        ->andReturn([
            'blocks' => $parentBlocks,
            'topics' => [],
        ]);

    $response = (new EventResponseBuilder($entryBuilder))->build($linked);

    expect($response['blocks'])->toBe($parentBlocks);
});
