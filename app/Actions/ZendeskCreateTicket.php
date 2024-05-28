<?php

namespace App\Actions;

use App\Models\Issue;
use Illuminate\Support\Facades\Http;
use Lorisleiva\Actions\Concerns\AsJob;

class ZendeskCreateTicket
{
    use AsJob;

    public function handle(Issue $issue): void
    {
        if (app()->isProduction()) {

            $payload = [
                'subject' => $issue->subject,
                'description' => $issue->description,
                'requester' => [
                    'name' => $issue->name,
                    'email' => $issue->email,
                ],
            ];

            $response = Http::withBasicAuth(config('services.zendesk.user').'/token', config('services.zendesk.token'))
                ->post(config('services.zendesk.ticket_api'), ['ticket' => $payload]);

            $statusCode = $response->status();
            $responseData = $response->json();
        } else {
            ray('Zendesk ticket creation is disabled in non-production environments.', $issue->toArray());
        }
    }
}
