<?php

namespace Database\Factories;

use App\Enums\EventTypeEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EventType>
 */
class EventTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'group_type' => 'fire',
            'type' => EventTypeEnum::Gathering,
            'sex' => 'men',
            'image' => 'test-image.jpg',
            'is_active' => true,
            'description' => fake()->paragraph(),
            'registration_welcome_video' => 'https://vimeo.com/505766418',
            'registration_welcome_text' => fake()->paragraph(),
            'registration_before_your_event_text' => fake()->paragraph(),
            'event_schedule' => [
                'name' => 'Schedule',
            ],
            'event_faqs' => [
                'name' => 'Schedule',
            ],
            'summary' => fake()->paragraph(),
            'summary_link' => fake()->url(),
        ];
    }

    public function inactive(): self
    {
        return $this->state(fn () => [
            'is_active' => false,
        ]);
    }
}
