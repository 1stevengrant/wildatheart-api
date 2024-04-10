<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            return;
        }

        $sql = file_get_contents(database_path('seeders/events.sql'));

        \DB::unprepared($sql);

        $user = User::factory()->create([
            'family_name' => 'Grant',
            'given_name' => 'Steven',
            'email' => 'steven@ghijk.co.uk',
            'password' => bcrypt('password'),
        ]);

        $user->assignRole(User::ROLE_ADMIN);

        User::factory()->create([
            'family_name' => 'Morrison',
            'given_name' => 'Jamie',
            'email' => 'jamie@wildatheart.org',
            'password' => bcrypt('password'),
        ]);

        DB::table('events')
            ->update([
                'user_id' => $user->id,
            ]);

    }
}
