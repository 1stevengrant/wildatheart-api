<?php

use App\Models\User;

beforeEach(function () {
    $this->seed(\Database\Seeders\DatabaseSeeder::class);
});
it('updates the user account', function () {
    $user = User::factory()->create();

    \App\Actions\User\UpdateUser::run(
        $user,
        [
            'name' => 'John Doe',
            'family_name' => 'Doe',
            'given_name' => 'John',
            'email' => 'steven@ghijk.co.uk',
        ]
    );

    $this->assertDatabaseHas('users', [
        'given_name' => 'John',
        'family_name' => 'Doe',
        'name' => 'John Doe',
        'email' => 'steven@ghijk.co.uk']);
});
