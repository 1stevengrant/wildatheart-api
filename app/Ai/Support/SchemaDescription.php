<?php

namespace App\Ai\Support;

use App\Models\EventType;
use Illuminate\Support\Facades\Schema;

/**
 * Describes the queryable part of the database for the platform agent.
 *
 * The output is cached for a day so the agent's instructions stay
 * byte-identical between prompts and benefit from prompt caching.
 */
class SchemaDescription
{
    /** @var array<int, string> */
    private const array EVENT_TYPE_COLUMNS = ['id', 'name', 'slug', 'type', 'group_type', 'sex', 'is_active', 'order'];

    /** @var array<int, string> */
    private const array NOTES = [
        'events.user_id is the host (owner) of the event. event_user links additional managers (co-hosts) to events.',
        'events.approved_at IS NOT NULL means the event is published. NULL means it is a draft or awaiting approval.',
        'events.is_private = 1 means the event is hidden from the public map.',
        'events.start_date is the date the event starts. Fires (recurring gatherings) usually have a start_date in the past and stay active while published.',
        'events.attendee_count is a denormalised count of attendees rows for the event.',
        'Country columns are inconsistent: ISO codes in any case ("us", "GB") and English names ("United States", "UK"). Match every form, e.g. country IN (\'us\', \'United States\', \'usa\'). Never use LIKE \'%us%\' (it matches Australia, Russia...).',
        'events.state holds either the state name or code ("Texas" or "TX"); events.state_code is usually empty. The states table maps US state names to codes.',
        'event_types.type is one of: gathering (fires), retreat, course (experiences). "Fires" usually means the Fires and Captivating Fires event types.',
        'attendees are signups for an event. attendees.created_at is the signup date. attendees.user_id is NULL for guests without an account.',
        'event_user_interests records users who marked interest in an event (archived_at set when dismissed).',
        'addresses belong to an event (event_id) or a user (user_id, is_primary marks the mailing address).',
        'Most tables use soft deletes: always add `deleted_at IS NULL` unless asked about deleted records.',
        'events.id is a UUID string. All other ids are integers.',
    ];

    public function toString(): string
    {
        return cache()->remember('ai.schema-description', now()->addDay(), fn () => $this->build());
    }

    private function build(): string
    {
        $tables = collect(ReadOnlySqlGuard::ALLOWED_TABLES)
            ->map(fn (string $table) => "- {$table}(" . implode(', ', $this->columnsFor($table)) . ')')
            ->implode("\n");

        $eventTypes = EventType::query()
            ->orderBy('id')
            ->get(['id', 'name', 'slug', 'type', 'is_active'])
            ->map(fn (EventType $eventType) => "- {$eventType->id}: {$eventType->name} (slug: {$eventType->slug}, type: {$eventType->type?->value}" . ($eventType->is_active ? '' : ', inactive') . ')')
            ->implode("\n");

        $notes = collect(self::NOTES)->map(fn (string $note) => "- {$note}")->implode("\n");

        return <<<TEXT
        Tables (MySQL 8):
        {$tables}

        Event types:
        {$eventTypes}

        Notes:
        {$notes}
        TEXT;
    }

    /**
     * @return array<int, string>
     */
    private function columnsFor(string $table): array
    {
        $columns = collect(Schema::getColumns($table))
            ->reject(fn (array $column) => in_array($column['name'], ReadOnlySqlGuard::SECRET_COLUMNS, true));

        if ($table === 'event_types') {
            $columns = $columns->filter(fn (array $column) => in_array($column['name'], self::EVENT_TYPE_COLUMNS, true));
        }

        return $columns
            ->map(fn (array $column) => "{$column['name']} {$column['type_name']}")
            ->values()
            ->all();
    }
}
