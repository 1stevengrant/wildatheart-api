<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Attendee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendee>
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
            'user_id' => User::factory()->create()->id,
            'given_name' => fake()->firstName(),
            'family_name' => fake()->lastName(),
            'email' => fake()->email(),
            'city' => fake()->city(),
            'country' => 'US',
            'state' => 'CA',
        ];
    }
}
