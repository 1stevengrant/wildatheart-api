<?php

namespace Database\Factories;

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
        ];
    }

    public function inactive(): self
    {
        return $this->state(fn () => [
            'is_active' => false,
        ]);
    }
}
