<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Statamic\Facades\Entry;
use Statamic\Facades\Collection;

uses(Tests\TestCase::class, RefreshDatabase::class)->in('Feature');

beforeEach(function () {
    // Ensure the 'pages' collection exists
    if (! Collection::handleExists('pages')) {
        Collection::make('pages')->save();
    }
});

it('returns 404 when page not found', function () {
    $nonExistentSlug = 'non-existent-page';

    $response = $this->getJson(route('content.v1.pages.bySlug', ['slug' => $nonExistentSlug]));

    $response->assertStatus(404)
        ->assertJson(['error' => 'Page not found']);
});

test('returns published page by slug', function () {
    $slug = 'test-page';

    // Create a published page entry
    $entry = Entry::make()
        ->collection('pages')
        ->blueprint('page')
        ->slug($slug)
        ->published(true)
        ->data([
            'title' => 'Test Page',
            'blocks' => [
                [
                    'type' => 'set',
                    'attrs' => [
                        'id' => 'm1z9u8bz',
                        'values' => [
                            'type' => 'hero_title',
                            'hero_title' => [
                                [
                                    'id' => 'm1z9u95t',
                                    'text' => 'This is a test page.',
                                    'type' => 'text_line',
                                    'enabled' => true,
                                    'text_style' => 'solid',
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            'date' => now()->subDay(),
        ])
        ->save();

    $response = $this->getJson(route('content.v1.pages.bySlug', ['slug' => $slug]));

    $response->assertStatus(200)
        ->assertJsonFragment([
            'text' => 'This is a test page.',
        ]);
});

test('it does not return unpublished pages', function () {
    $slug = 'unpublished-page';

    // Create an unpublished page entry
    $entry = Entry::make()
        ->collection('pages')
        ->slug($slug)
        ->published(false)
        ->data([
            'title' => 'Unpublished Page',
            'content' => 'This page is not published yet.',
            'date' => now()->subDay(),
        ])
        ->save();

    $response = $this->getJson(route('content.v1.pages.bySlug', ['slug' => $slug]));

    $response->assertStatus(404)
        ->assertJson(['error' => 'Page not found']);
});

test('it does not return pages with future date', function () {
    $slug = 'future-page';

    // Create a page with a future date
    $entry = Entry::make()
        ->collection('pages')
        ->slug($slug)
        ->published(true)
        ->date(now()->addDay())
        ->data([
            'title' => 'Future Page',
            'content' => 'This page is scheduled for future.',
        ])
        ->save();

    $response = $this->getJson(route('content.v1.pages.bySlug', ['slug' => $slug]));

    $response->assertStatus(404)
        ->assertJson(['error' => 'Page not found']);
});

test('it returns future page when make_scheduled_page_public is true', function () {
    $slug = 'future-public-page';

    // Create a future page with 'make_scheduled_page_public' set to true
    $entry = Entry::make()
        ->collection('pages')
        ->slug($slug)
        ->published(true)
        ->date(now()->addDay())
        ->data([
            'title' => 'Future Public Page',
            'blocks' => [
                [
                    'type' => 'set',
                    'attrs' => [
                        'id' => 'm1z9u8bz',
                        'values' => [
                            'type' => 'hero_title',
                            'hero_title' => [
                                [
                                    'id' => 'm1z9u95t',
                                    'text' => 'This page is scheduled but should be public.',
                                    'type' => 'text_line',
                                    'enabled' => true,
                                    'text_style' => 'solid',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'make_scheduled_page_public' => true,
        ])
        ->save();

    $response = $this->getJson(route('content.v1.pages.bySlug', ['slug' => $slug]));

    $response->assertStatus(200)
        ->assertJsonFragment([
            'title' => 'Future Public Page',
            'text' => 'This page is scheduled but should be public.',
        ]);
});
