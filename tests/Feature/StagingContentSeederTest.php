<?php

use Statamic\Facades\Entry;
use Illuminate\Support\Facades\Queue;
use Database\Seeders\StagingContentSeeder;
use App\Jobs\Cloudflare\TriggerAstroBuildJob;

it('refuses to run outside local and staging', function () {
    config(['app.env' => 'production']);

    (new StagingContentSeeder)->run();
})->throws(RuntimeException::class, 'only allowed in local and staging');

describe('seeded content', function () {
    beforeEach(function () {
        config(['app.env' => 'staging']);

        (new StagingContentSeeder)->run();
    });

    it('imports the navigation from the global set files', function () {
        $navigation = $this->getJson('/api/v1/navs/all')->assertOk();

        expect($navigation->json('desktop'))->not->toBeEmpty()
            ->and($navigation->json('mobile.main_nav_bar'))->not->toBeEmpty();
    });

    it('serves every prayer with the route and title Playwright checks', function () {
        $prayers = collect($this->getJson('/api/content/v1/prayers')->assertOk()->json());

        expect($prayers)->toHaveCount(17)
            ->and($prayers->firstWhere('drupal_url', '/prayer/st-patricks-breastplate')['title'])
            ->toBe('St. Patrick’s Breastplate');
    });

    it('serves the series page', function () {
        $this->getJson('/api/content/v1/page/the-series')
            ->assertOk()
            ->assertJsonPath('blocks.0.hero_title.0.text', 'Stories of broken men becoming really good men.');
    });

    it('serves each series chapter from its series uri', function (string $slug, string $heroTitle) {
        $this->getJson("/api/content/v1/media-or-series/the-series/{$slug}")
            ->assertOk()
            ->assertJsonPath('blueprint.handle', 'custom_media_item')
            ->assertJsonPath('blocks.0.hero_title.0.text', $heroTitle)
            ->assertJsonPath('blocks.0.hero_title.0.text_style.value', 'hollow');
    })->with([
        ['prologue-john', 'Prologue'],
        ['chapter-1-mark', 'Mark'],
        ['chapter-2-pablo', 'Pablo'],
        ['chapter-3-leo', 'Leo'],
        ['chapter-4-sam', 'Sam'],
    ]);

    it('serves the retreat with its call to action buttons', function () {
        $this->getJson('/api/content/v1/events/captivating-retreat')
            ->assertOk()
            ->assertJsonPath('buttons.0.text', 'Attend A Retreat')
            ->assertJsonPath('buttons.0.href', 'https://local.wildatheart.org?types=3')
            ->assertJsonPath('buttons.1.text', 'Host A Retreat')
            ->assertJsonPath('buttons.1.href', 'https://local.wildatheart.org/host/select')
            ->assertJsonPath('buttons.0.left_icon.icon.key', 'arrowRight');
    });

    it('lists a book', function () {
        $this->getJson('/api/content/collections/books/entries')
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'staging-book');
    });

    it('can run again without duplicating entries or triggering an Astro build', function () {
        Queue::fake();

        $entryCount = Entry::query()->count();

        (new StagingContentSeeder)->run();

        expect(Entry::query()->count())->toBe($entryCount);

        Queue::assertNotPushed(TriggerAstroBuildJob::class);
    });
});
