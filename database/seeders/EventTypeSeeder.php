<?php

namespace Database\Seeders;

use App\Models\EventType;
use Illuminate\Database\Seeder;

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
            'image' => $this->getImagePath($eventType),
        ]));
    }

    protected function getImagePath(string $name): ?string
    {
        if ($name === EventType::FIRES) {
            return 'event_types/481aa244-1ba4-4522-8ac6-093a927b3526.jpg';
        }
        if ($name === EventType::WAH_BASIC) {
            return 'event_types/d5d2939b-df8a-4994-863e-4cd463c41ffb.jpg';
        }
        if ($name === EventType::CAPTIVATING_CORE_RETREAT) {
            return 'event_types/9a28e776-c9a3-4cf9-a112-f1fb1c3af6c0.jpg';
        }
        if ($name === EventType::BECOMING_A_KING) {
            return 'event_types/c8468f97-8e87-4e39-9b52-26b088415bdb.jpg';
        }
        if ($name === EventType::WAH_EXPERIENCE) {
            return 'event_types/18ef741e-6e63-4819-bd0c-95a92d6b80e5.jpg';
        }
        if ($name === EventType::CAPTIVATING_EXPERIENCE) {
            return 'event_types/b5113945-276c-4223-b59b-f273c0eecd73.jpg';
        }
        if ($name === EventType::BECOMING_A_KING_EXPERIENCE) {
            return 'event_types/2dacd65f-7782-4ba8-a25d-ffba260fa8c5.jpg';
        }
        if ($name === EventType::SURVIVAL_SCHOOL) {
            return 'event_types/5fae180b-fd9d-4ba8-8bac-73998008da3e.jpg';
        }

        return null;
    }
}
