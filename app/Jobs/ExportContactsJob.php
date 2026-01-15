<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Event;
use App\Data\AttendeeData;
use App\Mail\ReportMailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class ExportContactsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly User $user,
        private readonly ?string $eventId = null,
    ) {}

    public function handle(): void
    {
        $filePath = $this->generateCsv();

        Mail::to($this->user->email)->send(
            new ReportMailable(
                date: now()->format('F j, Y'),
                filePath: Storage::path($filePath),
                reportName: 'Contacts'
            )
        );

        Storage::delete($filePath);
    }

    private function generateCsv(): string
    {
        $contacts = $this->getContacts();
        $fileName = 'exports/contacts-' . now()->timestamp . '.csv';

        $handle = fopen('php://temp', 'w');

        fputcsv($handle, ['Name', 'Email', 'Phone', 'Event', 'Event Type', 'First contacted']);

        foreach ($contacts as $contact) {
            $contactData = AttendeeData::fromModel($contact);

            fputcsv($handle, [
                $contactData->fullname,
                $contactData->email,
                $contactData->phone,
                $contactData->event_name,
                $contactData->event_type_name,
                $contactData->created_at,
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        Storage::put($fileName, $content);

        return $fileName;
    }

    private function getContacts(): \Illuminate\Database\Eloquent\Collection
    {
        if ($this->eventId) {
            $event = Event::find($this->eventId);

            return $event->attendees()->with(['event.eventType'])->get();
        }

        return $this->user->contacts()->with(['event.eventType'])->get();
    }
}
