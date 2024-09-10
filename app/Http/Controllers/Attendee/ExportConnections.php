<?php

namespace App\Http\Controllers\Attendee;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;

class ExportConnections extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {
        $eventId = request('event');
        $csvFileName = 'connections'.now()->timestamp.'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$csvFileName.'"',
        ];

        $handle = fopen('php://output', 'w');
        fputcsv($handle, ['Name', 'Email', 'Phone']);

        if ($eventId) {
            $event = \App\Models\Event::find($eventId);
            $connections = $event->attendees;
        }

        if (! $eventId) {
            $connections = auth()->user()->connections;
        }

        foreach ($connections as $connection) {
            fputcsv($handle, [$connection->fullname, $connection->email, $connection->phone]);
        }

        fclose($handle);

        return Response::make('', 200, $headers);
    }
}
