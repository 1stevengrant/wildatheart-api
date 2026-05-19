<?php

use App\Models\User;
use App\Actions\User\UpdateUser;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Bus::fake();
    Mail::fake();
});

it('updates the user account', function () {
    $user = User::factory()->create();

    UpdateUser::run(
        $user,
        [
            'name' => 'John Doe',
            'family_name' => 'Doe',
            'given_name' => 'John',
            'email' => 'steven@example.com',
        ]
    );

    $this->assertDatabaseHas('users', [
        'given_name' => 'John',
        'family_name' => 'Doe',
        'name' => 'John Doe',
        'email' => 'steven@example.com']);
});
