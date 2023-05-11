<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CampFire>
 */
class CampFireFactory extends Factory
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
            'date' => $this->faker->dateTimeBetween('-1 year', '+1 year'),
            'time' => $this->faker->randomElement(generateTimeList()),
            'attendee_limit' => $this->faker->numberBetween(1, 100),
            'longitude' => $this->faker->longitude,
            'latitude' => $this->faker->latitude,
            'approved_at' => $this->faker->boolean() ? $this->faker->dateTimeBetween('-1 year', '+1 year') : null,
        ];
    }
}
