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
    public function __construct(public $experience) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $event = Event::updateOrCreate(
            ['legacy_drupal_id' => $this->experience['nid']],
            [
                'event_type_id' => $this->getEventType($this->experience['type']),
                'approved_at' => $this->getStatus($this->experience['is_published']),
                'name' => strip_tags($this->experience['title']),
                'summary' => strip_tags($this->experience['body']),
                'legacy_drupal_id' => $this->experience['nid'],
                'legacy_drupal_user_id' => $this->experience['user_id'],
                'user_id' => $this->updateOrCreateUser(
                    $this->experience['email'],
                    $this->experience['first_name'],
                    $this->experience['last_name'],
                    $this->experience['phone']
                ),
                'legacy_drupal_location' => $this->experience['location'],
                'legacy_drupal_url' => $this->experience['url'],
                'start_date' => Carbon::createFromFormat('Y-m-d', $this->experience['start_date']),
                'end_date' => $this->getEndDate($this->experience['end_date']),
                'created_at' => Carbon::createFromFormat('Y-m-d H:i:s', $this->experience['posted_on']),
                'sex' => $this->determineEventSex($this->experience['title']),
            ]
        );

        dispatch(new UpdateEvent($event));
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
            return EventType::where('name', EventType::WAH_BASIC)->first()->id;
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

    public function determineEventSex($haystack): string
    {
        return str_contains(strtolower($haystack), 'captivating') ? Event::FOR_WOMEN : Event::FOR_MEN;
    }
}
