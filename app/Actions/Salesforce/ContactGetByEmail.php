<?php

namespace App\Actions\Salesforce;

use Lorisleiva\Actions\Concerns\AsAction;
use Omniphx\Forrest\Providers\Laravel\Facades\Forrest;

class ContactGetByEmail
{
    use AsAction;

    public function handle(string $email): void
    {
        $result = Forrest::query("SELECT Id, FirstName, LastName FROM Contact WHERE Email = '$email'");

        if (empty($result['records'])) {
            $response = CreateContact::make()->handle([
                'FirstName' => 'Steven',
                'LastName' => 'Steven',
                'Email' => $email,
                'Phone' => '+447766494949',
            ]);
        } else {
            $salesforceId = $result['records'][0]['Id'];

        }
    }
}
