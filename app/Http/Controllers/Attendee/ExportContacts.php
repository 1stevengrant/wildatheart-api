<?php

namespace App\Http\Controllers\Attendee;

use App\Data\AttendeeData;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;

class ExportContacts extends Controller
{
    public function __invoke()
    {
        $eventId = request('event');
        $csvFileName = 'contacts' . now()->timestamp . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $csvFileName . '"',
        ];

        $handle = fopen('php://output', 'w');
        fputcsv($handle, ['Name', 'Email', 'Phone', 'Event', 'Event Type', 'First contacted']);

        if ($eventId) {
            $event = \App\Models\Event::find($eventId);
            $contacts = $event->attendees()->with(['event.eventType'])->get();
        }

        if (! $eventId) {
            $contacts = auth()->user()->contacts()->with(['event.eventType'])->get();
        }

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

        fclose($handle);

        return Response::make('', 200, $headers);
    }
}
