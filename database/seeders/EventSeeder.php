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

        $user = User::factory()->create();

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
        $days = now()->subDays(100);
        Event::factory()->count(50)->approved()->fires()->create([
            'user_id' => $user->id,
            'updated_at' => $days,
            'created_at' => $days,
            'approved_at' => $days,
        ]);

    }
}
