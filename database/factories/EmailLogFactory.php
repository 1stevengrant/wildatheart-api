<?php

namespace Database\Factories;

use App\Models\EmailLog;
use App\Enums\EmailLogStatus;
use App\Mail\Event\AttendeeRegistered;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmailLog>
 */
class EmailLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'delivery_status' => EmailLogStatus::Sent,
            'mailable' => AttendeeRegistered::class,
            'subject' => fake()->sentence(),
            'from' => fake()->safeEmail(),
            'to' => fake()->safeEmail(),
            'html_body' => '<p>' . fake()->paragraph() . '</p>',
            'attachments' => [],
            'mailer' => 'failover',
            'message_id' => fake()->uuid() . '@example.com',
        ];
    }

    public function failed(): static
    {
        return $this->state(fn (): array => [
            'delivery_status' => EmailLogStatus::Failed,
            'html_body' => null,
            'message_id' => null,
            'error' => 'Connection could not be established.',
        ]);
    }
}
