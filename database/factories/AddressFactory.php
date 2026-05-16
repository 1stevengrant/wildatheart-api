<?php

namespace Database\Factories;

use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'given_name' => fake()->firstName,
            'family_name' => fake()->lastName,
            'street_address' => fake()->streetAddress,
            'company' => fake()->company,
            'city' => fake()->city,
            'state' => 'UT',
            'zip' => fake()->postcode,
            'country' => fake()->country,
            'phone' => fake()->phoneNumber,
            'is_primary' => false,
        ];
    }
}
