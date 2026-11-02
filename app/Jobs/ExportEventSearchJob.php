<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Event;
use App\Mail\ReportMailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Facades\Storage;
use App\Enums\Internal\EventSearchField;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Enums\Internal\EventVisibilityFilter;

class ExportEventSearchJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 300;

    /**
     * @param  array<int, int>  $eventTypeIds
     * @param  array<int, string>  $fields
     */
    public function __construct(
        private readonly User $user,
        private readonly ?float $lat,
        private readonly ?float $lng,
        private readonly ?float $radius,
        private readonly array $eventTypeIds,
        private readonly array $fields,
        private readonly bool $activeOnly,
        private readonly bool $includeDeleted,
        private readonly ?string $createdFrom = null,
        private readonly ?string $createdTo = null,
        private readonly EventVisibilityFilter $privacy = EventVisibilityFilter::All,
    ) {}

    public function handle(): void
    {
        $filePath = $this->generateCsv();

        try {
            Mail::to($this->user->email)->send(
                new ReportMailable(
                    date: now()->format('F j, Y'),
                    filePath: Storage::disk('local')->path($filePath),
                    reportName: 'Event Search Export'
                )
            );
        } finally {
            Storage::disk('local')->delete($filePath);
        }
    }

    private function generateCsv(): string
    {
        Storage::disk('local')->makeDirectory('exports');

        $fileName = 'exports/event-search-' . now()->timestamp . '.csv';
        $fields = collect($this->fields)
            ->map(fn (string $field) => EventSearchField::tryFrom($field))
            ->filter();

        $handle = fopen(Storage::disk('local')->path($fileName), 'w');

        try {
            fputcsv($handle, $fields->map->label()->all(), ',', '"', '');

            $this->searchEvents()->each(function (Event $event) use ($handle, $fields) {
                fputcsv(
                    $handle,
                    $fields->map(fn (EventSearchField $field) => $this->sanitizeCsvValue($field->getValue($event)))->all(),
                    ',',
                    '"',
                    ''
                );
            });
        } finally {
            fclose($handle);
        }

        return $fileName;
    }

    /**
     * Sanitize a value to prevent CSV injection attacks.
     */
    private function sanitizeCsvValue(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        $firstChar = $value[0];

        if (in_array($firstChar, ['=', '+', '-', '@'], true)) {
            return "'" . $value;
        }

        return $value;
    }

    /** @return LazyCollection<int, Event> */
    private function searchEvents(): LazyCollection
    {
        $query = Event::query()->with(['user.primaryAddress', 'eventType']);

        if ($this->lat !== null && $this->lng !== null && $this->radius !== null) {
            $query->withinRadius($this->lat, $this->lng, $this->radius);
        }

        if ($this->activeOnly) {
            $query->published();
        }

        $this->privacy->applyTo($query);

        if ($this->includeDeleted) {
            $query->withTrashed();
        }

        if (! empty($this->eventTypeIds)) {
            $query->whereIn('event_type_id', $this->eventTypeIds);
        }

        if ($this->createdFrom) {
            $query->where('created_at', '>=', $this->createdFrom);
        }

        if ($this->createdTo) {
            $query->where('created_at', '<=', "{$this->createdTo} 23:59:59");
        }

        return $query->orderBy('events.id')->lazy();
    }
}
