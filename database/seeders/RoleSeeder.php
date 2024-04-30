<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // if roles table is not empty, do not seed
        if (DB::table('roles')->count() > 0) {
            return;
        }
        Role::create(['name' => User::ROLE_ADMIN]);
        Role::create(['name' => User::ROLE_HOST]);
        Role::create(['name' => User::ROLE_ATTENDEE]);

        $admin = User::factory()->create([
            'email' => 'jamie@wildatheart.org',
            'password' => bcrypt('password'),
            'family_name' => 'Admin',
            'given_name' => 'Jamie',
        ]);

        $admin->assignRole(User::ROLE_ADMIN);
    }
}
