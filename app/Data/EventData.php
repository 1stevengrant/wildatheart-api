<?php

namespace App\Data;

use Carbon\Carbon;
use App\Models\Event;
use App\Enums\EventSexEnum;
use Spatie\LaravelData\Data;
use App\Enums\Event\FrequencyEnum;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class EventData extends Data
{
    public function __construct(
        public string $id,
        public ?string $name,
        public float $longitude,
        public float $latitude,
        public string $badge_display,
        public float $display_longitude,
        public float $display_latitude,
        public ?string $display_name,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?Carbon $start_date,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?Carbon $startDate,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?Carbon $end_date,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?Carbon $endDate,
        public ?int $event_type_id,
        public ?string $event_type_name,
        public EventTypeData $event_type,
        public string $event_type_image,
        public ?string $type_group,
        public string $location,
        public ?string $city,
        public ?string $state,
        public ?string $country,
        public string $host,
        public string $host_name,
        public ?string $host_friendly_name,
        public ?int $host_id,
        public string $host_photo,
        public EventSexEnum $sex,
        public string $url,
        public int $group_size,
        #[WithCast(EnumCast::class)]
        public ?FrequencyEnum $frequency,
        public ?string $hero_image,
        public ?string $hero_video,
        public bool $is_private,
        public bool $gift_requested,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?Carbon $approved_at,
        public HostData $host_object,
        public EventTypeData $event_type_data,
        public string $card_title,
        public string $event_type_slug,
        public ?string $region = null,
        public ?string $district = null,
        public ?string $locality = null,
        public ?string $neighborhood = null,
        public ?string $street_address = null,
        public ?string $zip = null,
        public bool $is_featured = false,
        public ?string $custom_landing_url = null,
    ) {}

    public static function fromModel(Event $event): self
    {
        return new self(
            id: $event->id,
            name: $event->name,
            longitude: $event->longitude,
            latitude: $event->latitude,
            badge_display: $event->badge_display,
            display_longitude: $event->display_longitude ?? $event->longitude,
            display_latitude: $event->display_latitude ?? $event->latitude,
            display_name: $event->display_name ?? null,
            start_date: $event->start_date ? ($event->start_date instanceof Carbon ? $event->start_date : Carbon::parse($event->start_date)) : null,
            startDate: $event->startDate ? ($event->startDate instanceof Carbon ? $event->startDate : Carbon::parse($event->startDate)) : null,
            end_date: $event->end_date ? ($event->end_date instanceof Carbon ? $event->end_date : Carbon::parse($event->end_date)) : null,
            endDate: $event->endDate ? ($event->endDate instanceof Carbon ? $event->endDate : Carbon::parse($event->endDate)) : null,
            event_type_id: $event->event_type_id ?? null,
            event_type_name: $event->eventType->name ?? null,
            event_type: EventTypeData::fromModel($event->eventType),
            event_type_image: $event->eventType->image,
            type_group: $event->eventType?->group_type,
            location: $event->location,
            city: $event->city ?? null,
            state: $event->state ?? null,
            country: $event->country,
            host: $event->host_name,
            host_name: $event->host_name,
            host_friendly_name: $event->user->given_name ?? null,
            host_id: $event->user->id,
            host_photo: $event->user->profilePhoto(),
            sex: $event->sex,
            url: $event->url,
            group_size: $event->group_size ?? 1,
            frequency: $event->frequency,
            hero_image: $event->hero_image ?? null,
            hero_video: $event->hero_video ?? null,
            is_private: $event->is_private ?? false,
            gift_requested: $event->hasRequestedGift() ?? false,
            approved_at: $event->approved_at,
            host_object: HostData::fromModel($event->user),
            event_type_data: EventTypeData::fromModel($event->eventType),
            card_title: $event->card_title,
            event_type_slug: $event->eventType->slug,
            region: $event->region,
            district: $event->district,
            locality: $event->locality,
            neighborhood: $event->neighborhood,
            street_address: $event->street_address,
            zip: $event->zip,
            is_featured: $event->isFeatured(),
            custom_landing_url: $event->custom_landing_url,
        );
    }
}
