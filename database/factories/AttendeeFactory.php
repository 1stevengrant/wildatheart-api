<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendee>
 */
class AttendeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'campfire_id' => \App\Models\Campfire::query()->inRandomOrder()->first()->id,
            'phone' => $this->faker->phoneNumber,
            'user_id' => User::factory()->create()->id,
        ];
    }
}
