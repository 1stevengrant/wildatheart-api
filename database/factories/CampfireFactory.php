<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Campfire>
 */
class CampfireFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'description' => $this->faker->text,
            'phone' => $this->faker->phoneNumber,
            'street_address' => $this->faker->streetAddress,
            'state' => 'Montana',
            'state_code' => 'MT',
            'city' => $this->faker->city,
            'zip' => $this->faker->postcode,
            'attendee_limit' => $this->faker->numberBetween(1, 100),
            'longitude' => $this->faker->longitude,
            'latitude' => $this->faker->latitude,
            'book_request_count' => $this->faker->boolean ? $this->faker->numberBetween(1, 100) : 0,
            'is_private' => $this->faker->boolean,
            'user_id' => User::factory()->create()->id,
            'country' => 'US',
            'name' => $this->faker->sentence,
            'frequency' => $this->faker->randomElement(['weekly', 'monthly', 'quarterly']),
        ];
    }

    public function private(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'is_private' => true,
            ];
        });
    }

    public function approved(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'approved_at' => now(),
                'approved_by' => 1,
            ];
        });
    }
}
