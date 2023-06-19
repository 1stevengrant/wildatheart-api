<?php

namespace App\Actions\Address;

use App\Models\Address;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateAddress
{
    use AsAction;

    public function handle(array $data)
    {
        return Address::create($data);
    }
}
