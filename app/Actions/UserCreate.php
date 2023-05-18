<?php

namespace App\Actions;

use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;

class UserCreate
{
    use AsAction;

    public function handle(array $data)
    {
        return User::create($data);
    }
}
