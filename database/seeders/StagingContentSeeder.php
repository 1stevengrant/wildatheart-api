<?php

namespace Database\Seeders;

use RuntimeException;
use Statamic\Facades\Site;
use Statamic\Facades\YAML;
use Statamic\Facades\Entry;
use Illuminate\Database\Seeder;
use Statamic\Facades\GlobalSet;
use Statamic\Contracts\Entries\Entry as EntryContract;

class StagingContentSeeder extends Seeder
{
    private const array GLOBAL_SETS = ['desktop_nav', 'mobile_nav', 'local_platform'];

    /** @var array<string, string> */
    private const array PRAYERS = [
        'daily-prayer-head-household' => 'Daily Prayer (Head of Household)',
        'breaking-soul-ties-wah-podcast' => 'Breaking Soul Ties (from WAH Podcast)',
        'one-praying-through-colossians' => 'As One - Praying Through Colossians',
        'become-good-soil-prayer' => 'Become Good Soil Prayer',
        'stasidailyprayer' => 'Daily Prayer (Stasi)',
        'prayer-consecration' => 'Prayer of Consecration',
        'prayer-freedom-habitual-sins' => 'Prayer for Freedom from Habitual Sins',
        'river-life-prayer' => 'River of Life Prayer',
        'st-patricks-breastplate' => 'St. Patrick’s Breastplate',
        'prayer-inner-healing' => 'Prayer for Inner Healing',
        'daily-prayer-extended-version' => 'Daily Prayer (Extended Version)',
        'prayer-receive-jesus-christ-savior' => 'Prayer to Receive Jesus Christ as Savior',
        'prayer-sexual-healing' => 'Prayer for Sexual Healing',
        'life-prayer' => 'The "Life" Prayer',
        'prayer-breaking-curses' => 'Prayer for Breaking Curses',
        'bedtime-prayer' => 'A Bedtime Prayer',
        'daily-prayer-john' => 'Daily Prayer (John)',
    ];

    /** @var array<string, string> */
    private const array SERIES_CHAPTERS = [
        'prologue-john' => 'Prologue',
        'chapter-1-mark' => 'Mark',
        'chapter-2-pablo' => 'Pablo',
        'chapter-3-leo' => 'Leo',
        'chapter-4-sam' => 'Sam',
    ];

    public function run(): void
    {
        if (! in_array(config('app.env'), ['local', 'staging'], true)) {
            throw new RuntimeException('Staging content is only allowed in local and staging environments.');
        }

        $this->seedGlobalSets();
        $this->seedPrayers();
        $this->seedSeriesPage();
        $this->seedSeriesChapters();
        $this->seedBook();
        $this->seedRetreatEvent();
    }

    private function seedGlobalSets(): void
    {
        foreach (self::GLOBAL_SETS as $handle) {
            $data = YAML::file(base_path("content/globals/{$handle}.yaml"))->parse()['data'] ?? [];

            $globalSet = GlobalSet::findByHandle($handle);
            $site = Site::default()->handle();

            ($globalSet->in($site) ?? $globalSet->makeLocalization($site))
                ->data($data)
                ->save();
        }
    }

    private function seedPrayers(): void
    {
        $order = 1;

        foreach (self::PRAYERS as $slug => $title) {
            $this->saveEntry('prayers', 'prayers', $slug, [
                'title' => $title,
                'summary' => 'Placeholder prayer for the staging environment.',
                'content' => '<p>Placeholder prayer text for the staging environment.</p>',
                'drupal_url' => "/prayer/{$slug}",
                'order' => $order++,
            ]);
        }
    }

    private function seedSeriesPage(): void
    {
        $this->saveEntry('pages', 'page', 'the-series', [
            'title' => 'The Series',
            'blocks' => [$this->heroTitleBlock('Stories of broken men becoming really good men.', 'solid')],
        ]);
    }

    private function seedSeriesChapters(): void
    {
        $series = $this->saveEntry('media_series', 'custom_series', 'the-series', [
            'title' => 'The Series',
        ]);

        $position = 1;

        foreach (self::SERIES_CHAPTERS as $slug => $heroTitle) {
            $this->saveEntry('media_item', 'custom_media_item', $slug, [
                'title' => $heroTitle,
                'position' => $position++,
                'series' => "entry::{$series->id()}",
                'blocks' => [$this->heroTitleBlock($heroTitle, 'hollow')],
            ]);
        }
    }

    private function seedBook(): void
    {
        $this->saveEntry('books', 'book', 'staging-book', [
            'title' => 'Staging Book',
            'description' => '<p>Placeholder book for the staging environment.</p>',
            'authors' => [],
            'topics' => [],
        ]);
    }

    private function seedRetreatEvent(): void
    {
        $this->saveEntry('events', 'multiday_retreat', 'captivating-retreat', [
            'title' => 'Captivating Retreat',
            'show_in_collections' => true,
            'blocks' => [],
            'topics' => [],
            'buttons' => [
                $this->button('Attend A Retreat', 'https://local.wildatheart.org?types=3'),
                $this->button('Host A Retreat', 'https://local.wildatheart.org/host/select'),
            ],
        ]);
    }

    /** @param array<string, mixed> $data */
    private function saveEntry(string $collection, string $blueprint, string $slug, array $data): EntryContract
    {
        $entry = Entry::query()
            ->where('collection', $collection)
            ->where('slug', $slug)
            ->first() ?? Entry::make()->collection($collection)->slug($slug);

        $entry->blueprint($blueprint)
            ->published(true)
            ->data($data);

        if ($entry->collection()->dated()) {
            $entry->date(now()->subDay()->startOfDay());
        }

        $entry->saveQuietly();

        return $entry;
    }

    /** @return array<string, mixed> */
    private function heroTitleBlock(string $text, string $textStyle): array
    {
        return [
            'type' => 'set',
            'attrs' => [
                'id' => mb_substr(md5($text), 0, 8),
                'values' => [
                    'type' => 'hero_title',
                    'hero_title' => [
                        [
                            'id' => mb_substr(md5("{$text}-line"), 0, 8),
                            'text' => $text,
                            'type' => 'text_line',
                            'enabled' => true,
                            'text_style' => $textStyle,
                        ],
                    ],
                ],
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function button(string $text, string $href): array
    {
        return [
            'id' => mb_substr(md5($text), 0, 8),
            'type' => 'button',
            'enabled' => true,
            'text' => $text,
            'href' => $href,
            'variant' => 'primary',
            'left_icon' => ['icon' => 'arrowRight'],
        ];
    }
}
