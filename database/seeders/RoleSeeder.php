<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        DB::table('roles')->insert([
            ['name' => User::ROLE_ADMIN, 'guard_name' => 'web'],
            ['name' => User::ROLE_HOST, 'guard_name' => 'web'],
            ['name' => User::ROLE_ATTENDEE, 'guard_name' => 'web'],
        ]);
    }
}
