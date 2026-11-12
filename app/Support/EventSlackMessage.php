<?php

namespace App\Support;

use App\Models\User;
use App\Models\Event;
use App\Models\Attendee;

class EventSlackMessage
{
    /** @return array<int, array<string, mixed>> */
    public static function newEvent(Event $event): array
    {
        return self::blocks([
            self::header(':tada:', "New {$event->eventTypeSingular()}"),
            self::image($event),
            self::fields([
                'Name' => $event->name,
                'Dates' => $event->formatted_dates,
                'Location' => $event->location,
                'Host' => $event->host_name,
                'Host email' => $event->email,
            ]),
            self::actions([
                self::button('view_admin', 'View in Admin', $event->admin_url, primary: true),
                self::button('view_public', 'View Public Listing', route('view.event', $event)),
            ]),
        ]);
    }

    /** @return array<int, array<string, mixed>> */
    public static function attendeeContact(Attendee $attendee, Event $event): array
    {
        $origin = self::origin($attendee->city, getCountryName($attendee->country) ?? $attendee->country);

        $sentence = '*' . self::escape($attendee->fullname) . "*{$origin} wants to connect with *"
            . self::escape($event->host_name) . "* about their {$event->eventTypeSingular()}"
            . self::inLocation($event) . '.';

        return self::blocks([
            self::header(':wave:', 'Connect with Host request'),
            self::sentence($sentence, $event),
            self::eventFields($event),
        ]);
    }

    /** @return array<int, array<string, mixed>> */
    public static function userInterest(User $user, Event $event): array
    {
        $origin = self::origin($user->city);

        $sentence = '*' . self::escape($user->fullname) . "*{$origin} registered interest in *"
            . self::escape($event->host_name) . "*'s {$event->eventTypeSingular()}"
            . self::inLocation($event) . '.';

        return self::blocks([
            self::header(':raised_hand:', 'Interest registered'),
            self::sentence($sentence, $event),
            self::eventFields($event),
        ]);
    }

    /**
     * @param  array<int, ?array<string, mixed>>  $blocks
     * @return array<int, array<string, mixed>>
     */
    private static function blocks(array $blocks): array
    {
        return array_values(array_filter($blocks));
    }

    /** @return array<string, mixed> */
    private static function header(string $emoji, string $title): array
    {
        $prefix = app()->isProduction() ? '' : '[TEST] ';

        return [
            'type' => 'header',
            'text' => [
                'type' => 'plain_text',
                'text' => "{$prefix}{$emoji} {$title}",
                'emoji' => true,
            ],
        ];
    }

    /** @return ?array<string, mixed> */
    private static function image(Event $event): ?array
    {
        $imageUrl = self::eventTypeImageUrl($event, width: 600);

        if ($imageUrl === null) {
            return null;
        }

        return [
            'type' => 'image',
            'image_url' => $imageUrl,
            'alt_text' => $event->eventType->name,
        ];
    }

    /** @return array<string, mixed> */
    private static function sentence(string $text, Event $event): array
    {
        $section = [
            'type' => 'section',
            'text' => [
                'type' => 'mrkdwn',
                'text' => $text,
            ],
        ];

        $imageUrl = self::eventTypeImageUrl($event, width: 300);

        if ($imageUrl === null) {
            return $section;
        }

        return [
            ...$section,
            'accessory' => [
                'type' => 'image',
                'image_url' => $imageUrl,
                'alt_text' => $event->eventType->name,
            ],
        ];
    }

    /** @return ?array<string, mixed> */
    private static function eventFields(Event $event): ?array
    {
        return self::fields([
            'Event' => $event->name,
            'Dates' => $event->formatted_dates,
        ]);
    }

    /**
     * @param  array<string, ?string>  $labelledValues
     * @return ?array<string, mixed>
     */
    private static function fields(array $labelledValues): ?array
    {
        $fields = collect($labelledValues)
            ->filter(fn (?string $value): bool => filled($value))
            ->map(fn (string $value, string $label): array => [
                'type' => 'mrkdwn',
                'text' => "*{$label}:*\n" . self::escape($value),
            ])
            ->values()
            ->all();

        if ($fields === []) {
            return null;
        }

        return [
            'type' => 'section',
            'fields' => $fields,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $buttons
     * @return array<string, mixed>
     */
    private static function actions(array $buttons): array
    {
        return [
            'type' => 'actions',
            'elements' => $buttons,
        ];
    }

    /** @return array<string, mixed> */
    private static function button(string $actionId, string $label, string $url, bool $primary = false): array
    {
        $button = [
            'type' => 'button',
            'action_id' => $actionId,
            'text' => [
                'type' => 'plain_text',
                'text' => $label,
            ],
            'url' => $url,
        ];

        if (! $primary) {
            return $button;
        }

        return [...$button, 'style' => 'primary'];
    }

    private static function eventTypeImageUrl(Event $event, int $width): ?string
    {
        $image = $event->eventType?->new_image;

        if (blank($image)) {
            return null;
        }

        return bunnyImage($image, [
            'width' => $width,
            'quality' => 80,
            'format' => 'jpg',
        ]);
    }

    private static function origin(?string ...$parts): string
    {
        $origin = collect($parts)->filter(fn (?string $part): bool => filled($part))->implode(', ');

        if ($origin === '') {
            return '';
        }

        $escapedOrigin = self::escape($origin);

        return " ({$escapedOrigin})";
    }

    private static function inLocation(Event $event): string
    {
        if (blank($event->location)) {
            return '';
        }

        $escapedLocation = self::escape($event->location);

        return " in {$escapedLocation}";
    }

    private static function escape(string $text): string
    {
        return str_replace(['&', '<', '>'], ['&amp;', '&lt;', '&gt;'], $text);
    }
}
