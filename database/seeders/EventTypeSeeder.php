<?php

namespace Database\Seeders;

use App\Models\EventType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EventTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $eventTypes = collect([
            EventType::FIRES,
            EventType::WAH_BASIC,
            EventType::CAPTIVATING_CORE_RETREAT,
            EventType::BECOMING_A_KING,
            EventType::WAH_EXPERIENCE,
            EventType::CAPTIVATING_EXPERIENCE,
            EventType::BECOMING_A_KING_EXPERIENCE,
            EventType::SURVIVAL_SCHOOL,
        ]);

        $eventTypes->each(fn ($eventType) => EventType::create([
            'name' => $eventType,
            'summary' => fake()->sentence(5),
            'description' => fake()->paragraphs(6, true),
            'slug' => Str::slug($eventType),
            'image' => \Pest\Faker\fake()->imageUrl(),
        ]));
    }
}
