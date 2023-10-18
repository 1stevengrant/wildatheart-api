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
            'phone' => $this->faker->phoneNumber,
            'street_address' => $this->faker->streetAddress,
            'state' => 'Montana',
            'state_code' => 'MT',
            'city' => $this->faker->city,
            'zip' => $this->faker->postcode,
            'longitude' => $this->faker->longitude,
            'latitude' => $this->faker->latitude,
            'user_id' => User::factory()->create()->id,
            'country' => 'US',
            'name' => $this->faker->sentence,
            'frequency' => $this->faker->randomElement(['weekly', 'monthly', 'quarterly']),
        ];
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
