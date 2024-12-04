<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\EventType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Str;

class ImportEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public $drupalEvent) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $dispatcher = Event::getEventDispatcher();
        Event::unsetEventDispatcher();
        // Fetch the existing event by legacy_drupal_id
        $existingEvent = Event::where('legacy_drupal_id', $this->drupalEvent['nid'])->first();

        // Prepare the new data for comparison
        $newData = [
            'event_type_id' => $this->getEventType($this->drupalEvent['type']),
            'approved_at' => $this->getStatus($this->drupalEvent['is_published']),
            'name' => strip_tags($this->drupalEvent['title']),
            'summary' => strip_tags($this->drupalEvent['body']),
            'legacy_drupal_id' => $this->drupalEvent['nid'],
            'legacy_drupal_user_id' => $this->drupalEvent['user_id'],
            'user_id' => $this->updateOrCreateUser(
                $this->drupalEvent['email'],
                $this->drupalEvent['first_name'],
                $this->drupalEvent['last_name'],
                $this->drupalEvent['phone']
            ),
            'legacy_drupal_location' => $this->drupalEvent['location'],
            'legacy_drupal_url' => $this->drupalEvent['url'],
            'start_date' => Carbon::createFromFormat('Y-m-d', $this->drupalEvent['start_date']),
            'end_date' => $this->getEndDate($this->drupalEvent['end_date']),
            'created_at' => Carbon::createFromFormat('Y-m-d H:i:s', $this->drupalEvent['posted_on']),
            'sex' => determineEventSex($this->drupalEvent['title']),
            'is_private' => $this->determinePrivacySetting($this->drupalEvent['privacy_setting']),
        ];

        // If the event exists and the data hasn't changed, skip the update
        if ($existingEvent && empty(array_diff_assoc($newData, $existingEvent->toArray()))) {
            return;
        }

        // Otherwise, update or create the event
        $event = Event::updateOrCreate(
            ['legacy_drupal_id' => $this->drupalEvent['nid']],
            $newData
        );

        dispatch(new UpdateEvent($event));

        Event::setEventDispatcher($dispatcher);
    }

    public function updateOrCreateUser($email, $firstName, $lastName, $phone): int
    {
        return User::updateOrCreate(['email' => $email], [
            'family_name' => $lastName,
            'given_name' => $firstName,
            'email' => $email,
            'phone' => $phone,
            'password' => bcrypt(Str::random()),
        ])->id;
    }

    public function getEndDate($endDate): bool|Carbon|null
    {
        return $endDate !== 'null' ? Carbon::createFromFormat('Y-m-d', $endDate) : null;
    }

    public function getEventType($drupalEventType)
    {
        if ($drupalEventType === 'Becoming a King Retreat') {
            return EventType::where('name', EventType::BECOMING_A_KING)->first()->id;
        }
        if ($drupalEventType === 'Basic Event') {
            return EventType::where('name', EventType::WAH_RETREAT)->first()->id;
        }

        if ($drupalEventType === 'Core Event') {
            return EventType::where('name', EventType::CAPTIVATING_CORE_RETREAT)->first()->id;
        }

        if ($drupalEventType === 'Captivating Small Group') {
            return EventType::where('name', EventType::CAPTIVATING_EXPERIENCE)->first()->id;
        }

        if ($drupalEventType === 'Wild at Heart Small Group') {
            return EventType::where('name', EventType::WAH_EXPERIENCE)->first()->id;
        }

        if ($drupalEventType === 'Becoming a King Group') {
            return EventType::where('name', EventType::BECOMING_A_KING_EXPERIENCE)->first()->id;
        }

        return null;
    }

    public function getStatus($status): ?\Illuminate\Support\Carbon
    {
        return $status === 'true' ? now() : null;
    }

    public function determinePrivacySetting(string $privacySetting): bool
    {
        if (str_contains(strtolower($privacySetting), 'private')) {
            return true;
        }

        return false;
    }
}
