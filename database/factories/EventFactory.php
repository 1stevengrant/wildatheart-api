<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
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
            'sex' => $this->faker->randomElement([Event::FOR_MEN, Event::FOR_WOMEN]),
        ];
    }

    public function approved(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'approved_at' => now(),
            ];
        });
    }

    public function wahExperiences(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'event_type_id' => EventType::where('name', EventType::WAH_EXPERIENCE)->first()->id,
                'start_date' => now()->addDay(),
                'sex' => Event::FOR_MEN,
            ];
        });
    }

    public function captivatingExperience(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'event_type_id' => EventType::where('name', EventType::CAPTIVATING_EXPERIENCE)->first()->id,
                'start_date' => now()->addDay(),
                'sex' => 'f',
            ];
        });
    }

    public function becomingAKingExperience(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'event_type_id' => EventType::where('name', EventType::BECOMING_A_KING_EXPERIENCE)->first()->id,
                'start_date' => now()->addDay(),
                'sex' => Event::FOR_MEN,
            ];
        });
    }

    public function captivatingCoreRetreat(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'event_type_id' => EventType::where('name', EventType::CAPTIVATING_CORE_RETREAT)->first()->id,
                'start_date' => now()->addDay(),
                'end_date' => now()->addDays(3),
                'sex' => 'f',
            ];
        });
    }

    public function becomingAKingRetreat(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'event_type_id' => EventType::where('name', EventType::BECOMING_A_KING)->first()->id,
                'start_date' => now()->addDay(),
                'end_date' => now()->addDays(3),
                'sex' => Event::FOR_MEN,
            ];
        });
    }

    public function basicRetreat(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'event_type_id' => EventType::where('name', EventType::WAH_BASIC)->first()->id,
                'start_date' => now()->addDay(),
                'end_date' => now()->addDays(3),
                'sex' => Event::FOR_MEN,
            ];
        });
    }

    public function fires(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'event_type_id' => EventType::where('name', EventType::FIRES)->first()->id,
            ];
        });
    }

    public function survivalSchool(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'event_type_id' => EventType::where('name', EventType::SURVIVAL_SCHOOL)->first()->id,
                'start_date' => now()->addDay(),
                'end_date' => now()->addDays(3),
            ];
        });
    }
}
