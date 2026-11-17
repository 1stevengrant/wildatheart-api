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
use App\Data\InternalEventSearchCriteriaData;
use App\Actions\Event\BuildInternalEventSearchQuery;

class ExportEventSearchJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 120;

    /**
     * @param  array<int, string>  $fields
     */
    public function __construct(
        public readonly User $user,
        public readonly InternalEventSearchCriteriaData $criteria,
        public readonly array $fields,
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

            BuildInternalEventSearchQuery::run($this->criteria)
                ->orderBy('events.id')
                ->lazy()
                ->each(function (Event $event) use ($handle, $fields) {
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
}
