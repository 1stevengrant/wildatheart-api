<?php

namespace Database\Seeders;

use App\Models\Campfire;
use App\Models\User;
use Illuminate\Database\Seeder;

class CampFireSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sql = file_get_contents(database_path('seeders/campfires.sql'));
        \DB::unprepared($sql);
        $user = User::factory()->create();
        Campfire::all()->each(function ($campfire) use ($user) {
            $campfire->update([
                'user_id' => $user->id,
            ]);
        });
    }
}
