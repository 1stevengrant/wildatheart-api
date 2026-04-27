<?php

namespace Database\Seeders;

use App\Models\User;
use App\Enums\UserRoleEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // if the roles table is not empty, do not seed
        if (DB::table('roles')->count() > 0) {
            return;
        }
        Role::create(['name' => UserRoleEnum::Admin->value]);
        Role::create(['name' => UserRoleEnum::Host->value]);
        Role::create(['name' => UserRoleEnum::Attendee->value]);

        $admin = User::factory()->create([
            'email' => 'jamie@wildatheart.org',
            'password' => bcrypt('password'),
            'family_name' => 'Admin',
            'given_name' => 'Jamie',
        ]);

        $admin->assignRole(UserRoleEnum::Admin);
    }
}
