<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventType;
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
        $sql = file_get_contents(database_path('seeders/events.sql'));
        \DB::unprepared($sql);

        $user = User::factory()->create();
        $events = Event::withTrashed()->get()->pluck('id');
        DB::table('events')
            ->whereIn('id', $events)
            ->update([
                'user_id' => $user->id,
                'event_type_id' => EventType::where('name', EventType::FIRES)->first()->id,
            ]);

        Event::factory()->count(5)->approved()->wahExperiences()->create([
            'user_id' => $user->id,
        ]);

        Event::factory()->count(5)->approved()->captivatingExperience()->create([
            'user_id' => $user->id,
        ]);

        Event::factory()->count(5)->approved()->becomingAKingExperience()->create([
            'user_id' => $user->id,
        ]);

        Event::factory()->count(5)->approved()->captivatingCoreRetreat()->create([
            'user_id' => $user->id,
        ]);

        Event::factory()->count(5)->approved()->becomingAKingRetreat()->create([
            'user_id' => $user->id,
        ]);

        Event::factory()->count(5)->approved()->basicRetreat()->create([
            'user_id' => $user->id,
        ]);

        Event::factory()->count(5)->approved()->survivalSchool()->create([
            'user_id' => $user->id,
        ]);

    }
}
