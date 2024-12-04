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
            'name' => $this->faker->word(),
            'is_active' => true,
            'description' => $this->faker->paragraph(),
            'registration_welcome_video' => 'https://vimeo.com/505766418',
            'registration_welcome_text' => $this->faker->paragraph(),
            'registration_before_your_event_text' => $this->faker->paragraph(),
            'event_schedule' => [
                'name' => 'Schedule',
            ],
            'event_faqs' => [
                'name' => 'Schedule',
            ],
            'summary' => $this->faker->paragraph(),
        ];
    }

    public function inactive(): self
    {
        return $this->state(fn () => [
            'is_active' => false,
        ]);
    }
}
