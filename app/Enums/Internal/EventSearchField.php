<?php

namespace App\Enums\Internal;

use App\Models\Event;

enum EventSearchField: string
{
    case Name = 'name';
    case HostName = 'host_name';
    case HostEmail = 'host_email';
    case HostPhone = 'host_phone';
    case City = 'city';
    case State = 'state';
    case EventType = 'event_type';
    case Frequency = 'frequency';
    case AttendeeCount = 'attendee_count';
    case StartDate = 'start_date';
    case Distance = 'distance';
    case Status = 'status';
    case HostStreet = 'host_street';
    case HostAddressCity = 'host_address_city';
    case HostAddressState = 'host_address_state';
    case HostZip = 'host_zip';
    case HostCountry = 'host_country';
    case HostWeGiveId = 'host_wegive_id';

    public function label(): string
    {
        return match ($this) {
            self::Name => 'Event Name',
            self::HostName => 'Host Name',
            self::HostEmail => 'Host Email',
            self::HostPhone => 'Host Phone',
            self::City => 'City',
            self::State => 'State',
            self::EventType => 'Event Type',
            self::Frequency => 'Frequency',
            self::AttendeeCount => 'Attendee Count',
            self::StartDate => 'Start Date',
            self::Distance => 'Distance (miles)',
            self::Status => 'Status',
            self::HostStreet => 'Host Street',
            self::HostAddressCity => 'Host Address City',
            self::HostAddressState => 'Host Address State',
            self::HostZip => 'Host Zip',
            self::HostCountry => 'Host Country',
            self::HostWeGiveId => 'Host WeGive ID',
        };
    }

    public function isDefault(): bool
    {
        return match ($this) {
            self::Name,
            self::HostName,
            self::HostEmail,
            self::City,
            self::State,
            self::EventType,
            self::Distance => true,
            self::HostPhone,
            self::Frequency,
            self::AttendeeCount,
            self::StartDate,
            self::Status,
            self::HostStreet,
            self::HostAddressCity,
            self::HostAddressState,
            self::HostZip,
            self::HostCountry,
            self::HostWeGiveId => false,
        };
    }

    public function getValue(Event $event): ?string
    {
        return match ($this) {
            self::Name => $event->name,
            self::HostName => $event->user?->name,
            self::HostEmail => $event->user?->email,
            self::HostPhone => $event->user?->phone,
            self::City => $event->city,
            self::State => $event->state,
            self::EventType => $event->eventType?->name,
            self::Frequency => $event->frequency?->value,
            self::AttendeeCount => (string) $event->attendee_count,
            self::StartDate => $event->start_date?->format('Y-m-d'),
            self::Distance => isset($event->distance) ? (string) round($event->distance, 2) : null,
            self::Status => self::getEventStatus($event),
            self::HostStreet => $event->user?->primaryAddress()?->street_address,
            self::HostAddressCity => $event->user?->primaryAddress()?->city,
            self::HostAddressState => $event->user?->primaryAddress()?->state,
            self::HostZip => $event->user?->primaryAddress()?->zip,
            self::HostCountry => $event->user?->primaryAddress()?->country,
            self::HostWeGiveId => $event->user?->wegive_donor_id,
        };
    }

    /**
     * Get all fields as options for the frontend.
     *
     * @return array<int, array{key: string, label: string, default: bool}>
     */
    public static function toOptions(): array
    {
        return array_map(
            fn (self $field) => [
                'key' => $field->value,
                'label' => $field->label(),
                'default' => $field->isDefault(),
            ],
            self::cases()
        );
    }

    /**
     * Get the default field keys.
     *
     * @return array<int, string>
     */
    public static function defaults(): array
    {
        return array_map(
            fn (self $field) => $field->value,
            array_filter(self::cases(), fn (self $field) => $field->isDefault())
        );
    }

    private static function getEventStatus(Event $event): string
    {
        if ($event->trashed()) {
            return 'Deleted';
        }

        if (! $event->approved_at) {
            return 'Unpublished';
        }

        return 'Published';
    }
}
