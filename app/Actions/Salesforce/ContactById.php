<?php

namespace App\Actions\Salesforce;

use Lorisleiva\Actions\Concerns\AsAction;
use Omniphx\Forrest\Providers\Laravel\Facades\Forrest;

class ContactById
{
    use AsAction;

    public function handle(string $id)
    {
        $result = Forrest::sobjects('Contact/'.$id);

        return $result;
    }
}
