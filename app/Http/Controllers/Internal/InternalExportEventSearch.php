<?php

namespace App\Http\Controllers\Internal;

use App\Models\Event;
use App\Http\Controllers\Controller;
use App\Enums\Internal\EventSearchField;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Http\Requests\Internal\InternalEventSearchRequest;

class InternalExportEventSearch extends Controller
{
    public function __invoke(InternalEventSearchRequest $request): StreamedResponse
    {
        $events = $this->searchEvents(
            $request->lat(),
            $request->lng(),
            $request->radius(),
            $request->eventTypeIds()
        );

        $fields = $request->fields();
        $csvFileName = 'event-search-' . now()->timestamp . '.csv';

        return new StreamedResponse(function () use ($events, $fields) {
            $handle = fopen('php://output', 'w');

            $headerRow = [];
            foreach ($fields as $field) {
                $enumField = EventSearchField::tryFrom($field);
                if ($enumField) {
                    $headerRow[] = $enumField->label();
                }
            }
            fputcsv($handle, $headerRow);

            foreach ($events as $event) {
                $row = [];
                foreach ($fields as $field) {
                    $enumField = EventSearchField::tryFrom($field);
                    if ($enumField) {
                        $row[] = $this->sanitizeCsvValue($enumField->getValue($event));
                    }
                }
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $csvFileName . '"',
        ]);
    }

    /**
     * Sanitize a value to prevent CSV injection attacks.
     *
     * Prefixes cells starting with formula characters (=, +, -, @)
     * with a single quote to prevent spreadsheet applications from
     * interpreting them as formulas.
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

    private function searchEvents(float $lat, float $lng, float $radius, array $eventTypeIds): \Illuminate\Support\Collection
    {
        $query = Event::query()
            ->withinRadius($lat, $lng, $radius)
            ->with(['user', 'eventType'])
            ->published();

        if (! empty($eventTypeIds)) {
            $query->whereIn('event_type_id', $eventTypeIds);
        }

        return $query->get();
    }
}
