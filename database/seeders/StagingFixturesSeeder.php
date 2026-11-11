<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventType;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use App\Enums\Event\FrequencyEnum;
use Illuminate\Support\Facades\DB;
use App\Data\EventScheduleInputData;
use Illuminate\Support\Facades\Hash;
use App\Actions\Event\SyncEventSchedule;
use App\Data\EventScheduleDateInputData;

class StagingFixturesSeeder extends Seeder
{
    /** @var list<array{email: string, key: string}> */
    private const array HOSTS = [
        ['email' => 'jamiemorrison@wildatheart.org', 'key' => 'jamie'],
        ['email' => 'steven@ghijk.co.uk', 'key' => 'steven'],
        ['email' => 'wookie@wildatheart.org', 'key' => 'wookie'],
        ['email' => 'nic@wildatheart.org', 'key' => 'nic'],
    ];

    /** @var list<array{name: string, sex: string, city: string, state: string, state_code: string, longitude: float, latitude: float}> */
    private const array EVENT_TYPES = [
        ['name' => EventType::FIRES, 'sex' => 'men', 'city' => 'Bozeman', 'state' => 'Montana', 'state_code' => 'MT', 'longitude' => -111.0429, 'latitude' => 45.6770],
        ['name' => EventType::WAH_RETREAT, 'sex' => 'men', 'city' => 'Austin', 'state' => 'Texas', 'state_code' => 'TX', 'longitude' => -97.7431, 'latitude' => 30.2672],
        ['name' => EventType::CAPTIVATING_EXPERIENCE, 'sex' => 'women', 'city' => 'Nashville', 'state' => 'Tennessee', 'state_code' => 'TN', 'longitude' => -86.7816, 'latitude' => 36.1627],
    ];

    /**
     * Scheduled Fires for testing the schedule emails, all hosted by Jamie. Every run puts
     * them back in the window where the daily check does nothing (last date 22 days away),
     * so whoever tests can move them on with the Fire schedule tool at /internal/fire-schedules.
     *
     * @var list<array{frequency: FrequencyEnum, coHost: string, timezone: string, city: string, state: string, state_code: string, zip: string, country: string, longitude: float, latitude: float}>
     */
    private const array SCHEDULED_FIRES = [
        ['frequency' => FrequencyEnum::WEEKLY, 'coHost' => 'steven@ghijk.co.uk', 'timezone' => 'America/Denver', 'city' => 'Bozeman', 'state' => 'Montana', 'state_code' => 'MT', 'zip' => '59715', 'country' => 'US', 'longitude' => -111.0429, 'latitude' => 45.6770],
        ['frequency' => FrequencyEnum::MONTHLY, 'coHost' => 'wookie@wildatheart.org', 'timezone' => 'America/Chicago', 'city' => 'Austin', 'state' => 'Texas', 'state_code' => 'TX', 'zip' => '78701', 'country' => 'US', 'longitude' => -97.7431, 'latitude' => 30.2672],
        ['frequency' => FrequencyEnum::VARIES, 'coHost' => 'nic@wildatheart.org', 'timezone' => 'Pacific/Auckland', 'city' => 'Auckland', 'state' => 'Auckland', 'state_code' => 'AUK', 'zip' => '1010', 'country' => 'NZ', 'longitude' => 174.7633, 'latitude' => -36.8485],
    ];

    private const string SCHEDULED_FIRES_HOST = 'jamiemorrison@wildatheart.org';

    public function run(): void
    {
        $this->seed();
    }

    /** @return array{hosts: int, events: int, contacts: int, interests: int, attendees: int, scheduledFires: int} */
    public function seed(bool $dryRun = false): array
    {
        if (! in_array(config('app.env'), ['local', 'staging'], true)) {
            throw new \RuntimeException('Staging fixtures are only allowed in local and staging environments.');
        }

        if ($dryRun) {
            return [
                'hosts' => count(self::HOSTS),
                'events' => count(self::HOSTS) * count(self::EVENT_TYPES),
                'contacts' => count(self::HOSTS) * 6,
                'interests' => count(self::HOSTS) * count(self::EVENT_TYPES) * 6,
                'attendees' => count(self::HOSTS) * count(self::EVENT_TYPES) * 2,
                'scheduledFires' => count(self::SCHEDULED_FIRES),
            ];
        }

        $summary = ['hosts' => 0, 'events' => 0, 'contacts' => 0, 'interests' => 0, 'attendees' => 0, 'scheduledFires' => 0];

        DB::transaction(function () use (&$summary): void {
            foreach (self::HOSTS as $hostData) {
                $host = $this->fixtureHost($hostData['email']);

                $summary['hosts']++;
                $contacts = $this->seedContacts($hostData['key']);
                $summary['contacts'] += count($contacts);

                foreach (self::EVENT_TYPES as $eventTypeData) {
                    $eventType = DB::table('event_types')
                        ->where('name', $eventTypeData['name'])
                        ->where('is_active', true)
                        ->first();

                    if ($eventType === null) {
                        throw new \RuntimeException("Active staging fixture event type not found: {$eventTypeData['name']}");
                    }

                    $eventId = $this->seedEvent((int) $host->id, (int) $eventType->id, $hostData['key'], $eventTypeData);
                    $summary['events']++;
                    $summary['interests'] += $this->seedInterests($eventId, $contacts);
                    $summary['attendees'] += $this->seedAttendees($eventId, $contacts);

                    DB::table('events')->where('id', $eventId)->update([
                        'attendee_count' => DB::table('attendees')->where('event_id', $eventId)->count(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $summary['scheduledFires'] = $this->seedScheduledFires();
        });

        return $summary;
    }

    private function seedScheduledFires(): int
    {
        $host = $this->fixtureHost(self::SCHEDULED_FIRES_HOST);
        $eventType = DB::table('event_types')->where('name', EventType::FIRES)->where('is_active', true)->first();

        foreach (self::SCHEDULED_FIRES as $fire) {
            $eventId = $this->seedScheduledFireEvent((int) $host->id, (int) $eventType->id, $fire);

            DB::table('event_user')->where('event_id', $eventId)->delete();
            DB::table('event_user')->insert([
                'event_id' => $eventId,
                'user_id' => $this->fixtureHost($fire['coHost'])->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('event_schedule_notifications')->where('event_id', $eventId)->delete();
            DB::table('event_schedule_dates')->where('event_id', $eventId)->delete();

            SyncEventSchedule::run(Event::findOrFail($eventId), $this->quietSchedule($fire['frequency'], $fire['timezone']));
        }

        return count(self::SCHEDULED_FIRES);
    }

    /** @param array{frequency: FrequencyEnum, coHost: string, timezone: string, city: string, state: string, state_code: string, zip: string, country: string, longitude: float, latitude: float} $fire */
    private function seedScheduledFireEvent(int $hostId, int $eventTypeId, array $fire): string
    {
        $label = $fire['frequency'] === FrequencyEnum::VARIES ? 'Custom' : $fire['frequency']->label();
        $eventName = "[TEST] {$label} scheduled Fire hosted by jamie";
        $event = DB::table('events')->where('user_id', $hostId)->where('name', $eventName)->first();
        $attributes = [
            'event_type_id' => $eventTypeId,
            'sex' => 'men',
            'approved_at' => now(),
            'auto_unpublished' => false,
            'auto_unpublished_at' => null,
            'street_address' => '123 Example Street',
            'city' => $fire['city'],
            'state' => $fire['state'],
            'state_code' => $fire['state_code'],
            'zip' => $fire['zip'],
            'country' => $fire['country'],
            'longitude' => $fire['longitude'],
            'latitude' => $fire['latitude'],
            'description' => 'Example scheduled Fire for testing the schedule emails on local and staging.',
            'deleted_at' => null,
            'updated_at' => now(),
        ];

        if ($event !== null) {
            DB::table('events')->where('id', $event->id)->update($attributes);

            return (string) $event->id;
        }

        $eventId = (string) Str::uuid();

        DB::table('events')->insert([
            'id' => $eventId,
            'user_id' => $hostId,
            'name' => $eventName,
            ...$attributes,
            'created_at' => now(),
        ]);

        return $eventId;
    }

    /**
     * Four dates starting tomorrow in the Fire's timezone, so the last date is 22 days away
     * for Weekly and Custom (about three months for Monthly) and stays in the quiet window
     * for a few days after seeding.
     */
    private function quietSchedule(FrequencyEnum $frequency, string $timezone): EventScheduleInputData
    {
        $firstDate = CarbonImmutable::now($timezone)->addDay()->startOfDay();

        if ($frequency->autoRenews()) {
            return new EventScheduleInputData(
                frequency: $frequency,
                timezone: $timezone,
                default_start_time: '19:00',
                default_end_time: '21:00',
                anchor_date: $firstDate->toDateString(),
            );
        }

        return new EventScheduleInputData(
            frequency: $frequency,
            timezone: $timezone,
            default_start_time: '19:00',
            default_end_time: '21:00',
            dates: collect([0, 7, 14, 21])
                ->map(fn (int $days) => new EventScheduleDateInputData($firstDate->addDays($days)->toDateString()))
                ->all(),
        );
    }

    private function fixtureHost(string $email): object
    {
        $host = DB::table('users')->where('email', $email)->whereNull('deleted_at')->first();

        if ($host === null) {
            throw new \RuntimeException("Staging fixture host not found: {$email}");
        }

        return $host;
    }

    /** @return array<int, object> */
    private function seedContacts(string $hostKey): array
    {
        $contacts = [];

        for ($contactNumber = 1; $contactNumber <= 6; $contactNumber++) {
            $email = "test-contact-{$hostKey}-{$contactNumber}@example.test";
            $contact = DB::table('users')->where('email', $email)->first();
            $attributes = [
                'given_name' => 'Test',
                'family_name' => ucfirst($hostKey) . " Contact {$contactNumber}",
                'phone' => '+14065550' . mb_str_pad((string) $contactNumber, 3, '0', STR_PAD_LEFT),
                'country_code' => '1',
                'city' => 'Testville',
                'state' => 'Montana',
                'postal_code' => '59715',
                'email_verified_at' => now(),
                'agreed_to_host_agreement_date' => now(),
                'deleted_at' => null,
                'updated_at' => now(),
            ];

            if ($contact === null) {
                $id = DB::table('users')->insertGetId([
                    'uuid' => (string) Str::uuid(),
                    'email' => $email,
                    'password' => Hash::make('password'),
                    ...$attributes,
                    'created_at' => now(),
                ]);
                $contact = DB::table('users')->where('id', $id)->first();
            } else {
                DB::table('users')->where('id', $contact->id)->update($attributes);
                $contact = DB::table('users')->where('id', $contact->id)->first();
            }

            $contacts[$contactNumber] = $contact;
        }

        return $contacts;
    }

    /** @param array{name: string, sex: string, city: string, state: string, state_code: string, longitude: float, latitude: float} $eventTypeData */
    private function seedEvent(int $hostId, int $eventTypeId, string $hostKey, array $eventTypeData): string
    {
        $eventName = "[TEST] {$eventTypeData['name']} hosted by {$hostKey}";
        $event = DB::table('events')->where('user_id', $hostId)->where('name', $eventName)->first();
        $attributes = [
            'event_type_id' => $eventTypeId,
            'sex' => $eventTypeData['sex'],
            'frequency' => $eventTypeData['name'] === EventType::FIRES ? 'weekly' : null,
            'start_date' => now()->addDays(14),
            'end_date' => $eventTypeData['name'] === EventType::WAH_RETREAT ? now()->addDays(17) : null,
            'approved_at' => now(),
            'street_address' => '123 Example Street',
            'city' => $eventTypeData['city'],
            'state' => $eventTypeData['state'],
            'state_code' => $eventTypeData['state_code'],
            'zip' => '59715',
            'country' => 'US',
            'longitude' => $eventTypeData['longitude'],
            'latitude' => $eventTypeData['latitude'],
            'description' => 'Example fixture event for local and staging UI testing.',
            'deleted_at' => null,
            'updated_at' => now(),
        ];

        if ($event === null) {
            $eventId = (string) Str::uuid();
            DB::table('events')->insert([
                'id' => $eventId,
                'user_id' => $hostId,
                'name' => $eventName,
                ...$attributes,
                'created_at' => now(),
            ]);

            return $eventId;
        }

        DB::table('events')->where('id', $event->id)->update($attributes);

        return (string) $event->id;
    }

    /** @param array<int, object> $contacts */
    private function seedInterests(string $eventId, array $contacts): int
    {
        $timestamp = now();
        $rows = [];

        foreach ($contacts as $contactNumber => $contact) {
            $rows[] = [
                'user_id' => $contact->id,
                'event_id' => $eventId,
                'created_at' => $timestamp->copy()->subDays(7 - $contactNumber),
                'updated_at' => $timestamp,
                'archived_at' => $contactNumber === 6 ? $timestamp : null,
            ];
        }

        DB::table('event_user_interests')->upsert($rows, ['user_id', 'event_id'], ['updated_at', 'archived_at']);

        return count($rows);
    }

    /** @param array<int, object> $contacts */
    private function seedAttendees(string $eventId, array $contacts): int
    {
        $timestamp = now();

        foreach ([1, 2] as $contactNumber) {
            $contact = $contacts[$contactNumber];
            $existing = DB::table('attendees')->where('event_id', $eventId)->where('email', $contact->email)->first();
            $attributes = [
                'user_id' => $contact->id,
                'given_name' => $contact->given_name,
                'family_name' => $contact->family_name,
                'country' => 'US',
                'country_code' => '1',
                'phone' => $contact->phone,
                'city' => $contact->city,
                'state' => $contact->state,
                'marketing_updates' => true,
                'deleted_at' => null,
                'updated_at' => $timestamp,
            ];

            if ($existing === null) {
                DB::table('attendees')->insert([
                    'event_id' => $eventId,
                    'email' => $contact->email,
                    ...$attributes,
                    'created_at' => $timestamp,
                ]);
            } else {
                DB::table('attendees')->where('id', $existing->id)->update($attributes);
            }
        }

        return 2;
    }
}
