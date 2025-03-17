<?php

namespace App\Http\Controllers\Attendee;

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
        fputcsv($handle, ['Name', 'Email', 'Phone']);

        if ($eventId) {
            $event = \App\Models\Event::find($eventId);
            $contacts = $event->attendees;
        }

        if (! $eventId) {
            $contacts = auth()->user()->contacts;
        }

        foreach ($contacts as $contact) {
            fputcsv($handle, [$contact->fullname, $contact->email, $contact->phone]);
        }

        fclose($handle);

        return Response::make('', 200, $headers);
    }
}
