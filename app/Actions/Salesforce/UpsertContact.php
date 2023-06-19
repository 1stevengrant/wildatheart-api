<?php

namespace App\Actions\Salesforce;

use Lorisleiva\Actions\Concerns\AsAction;
use Omniphx\Forrest\Providers\Laravel\Facades\Forrest;

class UpsertContact
{
    use AsAction;

    public function handle(string $externalId, array $data): void
    {
        Forrest::sobjects('Contact/External_Id__c/'.$externalId, [
            'method' => 'patch',
            'body' => $data,
        ]);
    }
}
