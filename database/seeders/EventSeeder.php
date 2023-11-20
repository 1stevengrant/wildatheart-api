<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sql = file_get_contents(database_path('seeders/events.sql'));
        \DB::unprepared($sql);
        $user = User::factory()->create();
        Event::all()->each(function ($event) use ($user) {
            $event->update([
                'user_id' => $user->id,
            ]);
        });
    }
}
