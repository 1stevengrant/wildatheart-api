<?php

namespace App\Jobs;

use App\Models\User;
use App\Mail\ReportMailable;
use App\Data\V1\HostContactData;
use App\Models\EventUserInterest;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;

class HostContactsExportJob implements ShouldQueue
{
    use Queueable;

    /** @param array<string, mixed> $filters */
    /** @param array<int, array{event_id: string, user_id: int}> $selectedContacts */
    public function __construct(
        private readonly int $hostId,
        private readonly array $filters = [],
        private readonly array $selectedContacts = [],
    ) {}

    public function handle(): void
    {
        $filePath = $this->generateCsv();
        $host = User::findOrFail($this->hostId);

        Mail::to($host->email)->send(new ReportMailable(
            date: now()->format('F j, Y'),
            filePath: Storage::disk('local')->path($filePath),
            reportName: 'Contacts',
        ));

        Storage::disk('local')->delete($filePath);
    }

    private function generateCsv(): string
    {
        Storage::disk('local')->makeDirectory('exports');
        $fileName = 'exports/contacts-' . now()->timestamp . '.csv';
        $handle = fopen('php://temp', 'w');

        fputcsv($handle, ['Name', 'Email', 'Phone', 'Event', 'Event Type', 'First contacted']);

        foreach ($this->contacts() as $interest) {
            $contact = HostContactData::fromInterest($interest);
            fputcsv($handle, [
                mb_trim("{$contact->given_name} {$contact->family_name}"),
                $contact->email,
                $contact->phone,
                $contact->event_name,
                $contact->event_type_name,
                $contact->interested_at,
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);
        Storage::disk('local')->put($fileName, $content);

        return $fileName;
    }

    /** @return Collection<int, EventUserInterest> */
    private function contacts(): Collection
    {
        $host = User::findOrFail($this->hostId);
        $eventIds = $host->events()->pluck('events.id')
            ->merge($host->managedEvents()->pluck('events.id'))
            ->unique()
            ->values();

        $query = EventUserInterest::query()
            ->whereIn('event_id', $eventIds)
            ->with(['user', 'event.eventType']);

        if ($this->selectedContacts !== []) {
            $query->where(function (Builder $query): void {
                foreach ($this->selectedContacts as $contact) {
                    $query->orWhere(function (Builder $query) use ($contact): void {
                        $query->where('event_id', $contact['event_id'])
                            ->where('user_id', $contact['user_id']);
                    });
                }
            });

            return $query->get();
        }

        $this->applyFilters($query);

        return $query->get();
    }

    /** @param Builder<EventUserInterest> $query */
    private function applyFilters($query): void
    {
        $filters = $this->filters;

        if (! empty($filters['event'])) {
            $query->whereIn('event_id', explode(',', $filters['event']));
        }

        $archived = mb_strtolower((string) ($filters['archived'] ?? 'false'));
        if ($archived === 'true') {
            $query->whereNotNull('archived_at');
        } elseif ($archived !== 'all') {
            $query->whereNull('archived_at');
        }

        if (! empty($filters['added_from'])) {
            $query->whereDate('created_at', '>=', $filters['added_from']);
        }

        if (! empty($filters['added_to'])) {
            $query->whereDate('created_at', '<=', $filters['added_to']);
        }

        if (! empty($filters['search'])) {
            $search = '%' . mb_strtolower($filters['search']) . '%';
            $query->whereHas('user', function (Builder $query) use ($search): void {
                $query->whereRaw('LOWER(given_name) LIKE ?', [$search])
                    ->orWhereRaw('LOWER(family_name) LIKE ?', [$search])
                    ->orWhereRaw('LOWER(email) LIKE ?', [$search])
                    ->orWhereRaw('LOWER(phone) LIKE ?', [$search]);
            });
        }
    }
}
