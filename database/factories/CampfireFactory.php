<?php

namespace Database\Factories;

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
            'address' => $this->faker->address,
            'state' => $this->faker->state,
            'city' => $this->faker->city,
            'zip' => $this->faker->postcode,
            'date' => $this->faker->dateTimeBetween('-1 month', '+1 year'),
            'time' => $this->faker->randomElement(generateTimeList()),
            'attendee_limit' => $this->faker->numberBetween(1, 100),
            'longitude' => $this->faker->longitude,
            'latitude' => $this->faker->latitude,
            'book_request_count' => $this->faker->boolean ? $this->faker->numberBetween(1, 100) : 0,
            'is_private' => $this->faker->boolean,
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
