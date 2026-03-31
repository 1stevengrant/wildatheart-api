<?php

namespace Database\Seeders;

use App\Models\User;
use App\Enums\UserRoleEnum;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
        ]);
        $users = User::factory()->count(10)->create();
        $users->each(function ($user) {
            $user->assignRole(UserRoleEnum::Attendee);
        });
    }
}
