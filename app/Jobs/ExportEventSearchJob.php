<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Event;
use App\Mail\ReportMailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Enums\Internal\EventSearchField;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class ExportEventSearchJob implements ShouldQueue
{
    use Queueable;

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
    ) {}

    public function handle(): void
    {
        $filePath = $this->generateCsv();

        Mail::to($this->user->email)->send(
            new ReportMailable(
                date: now()->format('F j, Y'),
                filePath: Storage::path($filePath),
                reportName: 'Event Search Export'
            )
        );

        Storage::delete($filePath);
    }

    private function generateCsv(): string
    {
        $events = $this->searchEvents();
        $fileName = 'exports/event-search-' . now()->timestamp . '.csv';

        $handle = fopen('php://temp', 'w');

        $headerRow = [];
        foreach ($this->fields as $field) {
            $enumField = EventSearchField::tryFrom($field);
            if ($enumField) {
                $headerRow[] = $enumField->label();
            }
        }
        fputcsv($handle, $headerRow);

        foreach ($events as $event) {
            $row = [];
            foreach ($this->fields as $field) {
                $enumField = EventSearchField::tryFrom($field);
                if ($enumField) {
                    $row[] = $this->sanitizeCsvValue($enumField->getValue($event));
                }
            }
            fputcsv($handle, $row);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        Storage::put($fileName, $content);

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

    private function searchEvents(): \Illuminate\Support\Collection
    {
        $query = Event::query()->with(['user', 'eventType']);

        if ($this->lat !== null && $this->lng !== null && $this->radius !== null) {
            $query->withinRadius($this->lat, $this->lng, $this->radius);
        }

        if ($this->activeOnly) {
            $query->published();
        }

        if ($this->includeDeleted) {
            $query->withTrashed();
        }

        if (! empty($this->eventTypeIds)) {
            $query->whereIn('event_type_id', $this->eventTypeIds);
        }

        return $query->get();
    }
}
