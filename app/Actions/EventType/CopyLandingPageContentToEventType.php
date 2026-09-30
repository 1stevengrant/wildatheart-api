<?php

namespace App\Actions\EventType;

use App\Models\EventType;
use Illuminate\Support\Arr;
use Statamic\Facades\Entry;
use App\Services\EventResponseBuilder;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Copies the event page content from an event type's Statamic landing page onto the event type,
 * so the event detail page can be edited in one place. Only empty columns are filled, so it is
 * safe to run again and never overwrites content edited on the event type.
 */
class CopyLandingPageContentToEventType
{
    use AsAction;

    /*
     * The frontend used to borrow the matching experience's video for landing pages without a
     * background video. The URLs now live on the event type instead.
     */
    private const array BACKGROUND_VIDEO_BY_LANDING_PAGE_SLUG = [
        'captivating-fires' => 'https://player.vimeo.com/external/503998859.hd.mp4?s=f7c2544e5afd074618d8df2671b1f4489dce6903&profile_id=175',
        'the-captivating-experience' => 'https://player.vimeo.com/external/503998859.hd.mp4?s=f7c2544e5afd074618d8df2671b1f4489dce6903&profile_id=175',
        'fires' => 'https://player.vimeo.com/external/503912510.hd.mp4?s=814f24119f83c7c5e1a6a50dc1cf165d3af3c327&profile_id=175',
        'wild-at-heart-experience' => 'https://player.vimeo.com/external/503912510.hd.mp4?s=814f24119f83c7c5e1a6a50dc1cf165d3af3c327&profile_id=175',
        'becoming-a-king-experience' => 'https://player.vimeo.com/external/604971831.hd.mp4?s=552723151129b4a1b278f2547bf2c5da01d6571c&profile_id=175',
    ];

    public function __construct(private readonly EventResponseBuilder $eventResponseBuilder) {}

    public function handle(EventType $eventType): void
    {
        if (! $eventType->related_landing_page_id) {
            return;
        }

        $landingPage = Entry::find($eventType->related_landing_page_id);

        if (! $landingPage) {
            return;
        }

        $content = $this->contentFrom($this->eventResponseBuilder->build($landingPage));

        $emptyColumns = collect($content)
            ->filter(fn (mixed $value, string $column) => blank($eventType->{$column}))
            ->all();

        $eventType->forceFill($emptyColumns)->save();
    }

    /** @return array<string, mixed> */
    private function contentFrom(array $landingPage): array
    {
        $blocks = collect($landingPage['blocks'] ?? [])->filter();
        $heroTitle = $blocks->firstWhere('type', 'hero_title');
        $subtitle = $blocks->firstWhere('type', 'styled_text');

        return [
            'page_logo' => $this->assetPath($landingPage['event_logo'] ?? null),
            'page_background_video_url' => ($landingPage['background_video_url'] ?? null)
                ?: self::BACKGROUND_VIDEO_BY_LANDING_PAGE_SLUG[$landingPage['slug'] ?? ''] ?? null,
            'page_background_image' => $this->assetPath($landingPage['background_video_fallback_image'] ?? null),
            'page_topics' => collect($landingPage['topics'] ?? [])->pluck('slug')->filter()->values()->all() ?: null,
            'page_leader_text' => $landingPage['title'] ?? null,
            'page_hero_title' => $this->heroTitleLines($heroTitle['hero_title'] ?? []) ?: null,
            'page_hero_subtitle' => $this->plainText($subtitle['text'] ?? null),
            'page_featured_video_url' => $landingPage['featured_video_url'] ?? null,
            'page_featured_video_fallback_image' => $this->assetPath($landingPage['featured_video_fallback_image'] ?? null),
            'page_description' => $this->plainText($landingPage['description'] ?? null) ? $landingPage['description'] : null,
        ];
    }

    /** @return array<int, array{text: string, text_style: ?string}> */
    private function heroTitleLines(array $lines): array
    {
        return collect($lines)
            ->map(fn (array $line) => [
                'text' => $line['text'] ?? '',
                'text_style' => Arr::get($line, 'text_style.value', $line['text_style'] ?? null),
            ])
            ->filter(fn (array $line) => $line['text'] !== '')
            ->values()
            ->all();
    }

    /** An augmented asset (or asset id) as the path within its container, e.g. `files/events/logo.png`. */
    private function assetPath(mixed $asset): ?string
    {
        $id = is_array($asset) ? ($asset['id'] ?? null) : $asset;

        if (! is_string($id) || $id === '') {
            return null;
        }

        return str_contains($id, '::') ? explode('::', $id, 2)[1] : $id;
    }

    private function plainText(mixed $html): ?string
    {
        if (! is_string($html)) {
            return null;
        }

        $text = mb_trim(html_entity_decode(strip_tags($html)));

        return $text === '' ? null : $text;
    }
}
