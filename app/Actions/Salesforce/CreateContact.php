<?php

namespace App\Actions\Salesforce;

use Lorisleiva\Actions\Concerns\AsAction;
use Omniphx\Forrest\Providers\Laravel\Facades\Forrest;

class CreateContact
{
    use AsAction;

    public function handle(array $data): array|string
    {
        return Forrest::sobjects('Contact', [
            'method' => 'post',
            'body' => $data,
        ]);
    }
}
