<?php

namespace Database\Seeders;

use App\Models\CampFire;
use App\Models\User;
use Illuminate\Database\Seeder;

class CampFireSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CampFire::factory()->count(100)->create([
            'user_id' => User::inRandomOrder()->first()->id,
        ]);

        $locations = [
            [
                'location' => 'New York City, USA',
                'latitude' => 40.7128,
                'longitude' => -74.0060,
            ],
            [

                'location' => 'Paris, France',
                'latitude' => 48.8566,
                'longitude' => 2.3522,
            ],
            [
                'location' => 'Tokyo, Japan',
                'latitude' => 35.6895,
                'longitude' => 139.6917,
            ],
            [
                'location' => 'Sydney, Australia',
                'latitude' => -33.8651,
                'longitude' => 151.2099,
            ],
            [
                'location' => 'Cairo, Egypt',
                'latitude' => 30.0444,
                'longitude' => 31.2357,
            ],
            [

                'location' => 'Rio de Janeiro, Brazil',
                'latitude' => -22.9068,
                'longitude' => -43.1729,
            ],
            [
                'location' => 'Cape Town, South Africa',
                'latitude' => -33.9249,
                'longitude' => 18.4241,
            ],
            [
                'location' => 'Moscow, Russia',
                'latitude' => 55.7512,
                'longitude' => 37.6184,
            ],
            [
                'location' => 'Mumbai, India',
                'latitude' => 19.0760,
                'longitude' => 72.8777,
            ],
            [
                'location' => 'Vancouver, Canada',
                'latitude' => 49.2827,
                'longitude' => -123.1207,
            ],
        ];

        foreach ($locations as $location) {
            CampFire::create([
                'user_id' => 1,
                'description' => fake()->sentence,
                'phone' => fake()->phoneNumber(),
                'address' => $location['location'],
                'state' => 'NY',
                'city' => 'New York',
                'zip' => '10001',
                'date' => fake()->dateTimeBetween('+1 month', '+1 year'),
                'time' => fake()->time(),
                'approved_at' => fake()->boolean(50) ? fake()->dateTimeBetween('-1 month', 'now') : null,
                'attendee_limit' => fake()->numberBetween(1, 100),
                'latitude' => $location['latitude'],
                'longitude' => $location['longitude'],
            ]);
        }

        foreach ($locations as $location) {
            CampFire::create([
                'user_id' => 1,
                'description' => fake()->sentence,
                'phone' => fake()->phoneNumber(),
                'address' => $location['location'],
                'state' => 'NY',
                'city' => 'New York',
                'zip' => '10001',
                'date' => fake()->dateTimeBetween('+1 month', '+1 year'),
                'time' => fake()->time(),
                'approved_at' => fake()->boolean(50) ? fake()->dateTimeBetween('-1 month', 'now') : null,
                'attendee_limit' => fake()->numberBetween(1, 100),
                'latitude' => $location['latitude'],
                'longitude' => $location['longitude'],
            ]);
        }

        foreach ($locations as $location) {
            CampFire::create([
                'user_id' => 1,
                'description' => fake()->sentence,
                'phone' => fake()->phoneNumber(),
                'address' => $location['location'],
                'state' => 'NY',
                'city' => 'New York',
                'zip' => '10001',
                'date' => fake()->dateTimeBetween('+1 month', '+1 year'),
                'time' => fake()->time(),
                'approved_at' => fake()->boolean(50) ? fake()->dateTimeBetween('-1 month', 'now') : null,
                'attendee_limit' => fake()->numberBetween(1, 100),
                'latitude' => $location['latitude'],
                'longitude' => $location['longitude'],
            ]);
        }

        foreach ($locations as $location) {
            CampFire::create([
                'user_id' => 1,
                'description' => fake()->sentence,
                'phone' => fake()->phoneNumber(),
                'address' => $location['location'],
                'state' => 'NY',
                'city' => 'New York',
                'zip' => '10001',
                'date' => fake()->dateTimeBetween('+1 month', '+1 year'),
                'time' => fake()->time(),
                'approved_at' => fake()->boolean(50) ? fake()->dateTimeBetween('-1 month', 'now') : null,
                'attendee_limit' => fake()->numberBetween(1, 100),
                'latitude' => $location['latitude'],
                'longitude' => $location['longitude'],
            ]);
        }

        foreach ($locations as $location) {
            CampFire::create([
                'user_id' => 1,
                'description' => fake()->sentence,
                'phone' => fake()->phoneNumber(),
                'address' => $location['location'],
                'state' => 'NY',
                'city' => 'New York',
                'zip' => '10001',
                'date' => fake()->dateTimeBetween('+1 month', '+1 year'),
                'time' => fake()->time(),
                'approved_at' => fake()->boolean(50) ? fake()->dateTimeBetween('-1 month', 'now') : null,
                'attendee_limit' => fake()->numberBetween(1, 100),
                'latitude' => $location['latitude'],
                'longitude' => $location['longitude'],
            ]);
        }

        foreach ($locations as $location) {
            CampFire::create([
                'user_id' => 1,
                'description' => fake()->sentence,
                'phone' => fake()->phoneNumber(),
                'address' => $location['location'],
                'state' => 'NY',
                'city' => 'New York',
                'zip' => '10001',
                'date' => fake()->dateTimeBetween('+1 month', '+1 year'),
                'time' => fake()->time(),
                'approved_at' => fake()->boolean(50) ? fake()->dateTimeBetween('-1 month', 'now') : null,
                'attendee_limit' => fake()->numberBetween(1, 100),
                'latitude' => $location['latitude'],
                'longitude' => $location['longitude'],
            ]);
        }

        foreach ($locations as $location) {
            CampFire::create([
                'user_id' => 1,
                'description' => fake()->sentence,
                'phone' => fake()->phoneNumber(),
                'address' => $location['location'],
                'state' => 'NY',
                'city' => 'New York',
                'zip' => '10001',
                'date' => fake()->dateTimeBetween('+1 month', '+1 year'),
                'time' => fake()->time(),
                'approved_at' => fake()->boolean(50) ? fake()->dateTimeBetween('-1 month', 'now') : null,
                'attendee_limit' => fake()->numberBetween(1, 100),
                'latitude' => $location['latitude'],
                'longitude' => $location['longitude'],
            ]);
        }
    }
}
