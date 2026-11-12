<?php

use App\Models\User;
use App\Models\Event;
use App\Models\Attendee;
use App\Models\EventType;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use App\Jobs\Event\SendNewEventSlackAlert;
use Spatie\SlackAlerts\Facades\SlackAlert;
use App\Jobs\Admin\PostUserInterestToSlackJob;
use App\Jobs\Admin\PostAttendeeContactToSlackJob;

beforeEach(function () {
    Queue::fake();
    Mail::fake();
    SlackAlert::fake();

    config(['slack-alerts.slack_alerts_enabled' => true]);

    $this->eventType = EventType::factory()->create([
        'name' => 'Fireside Gatherings',
        'new_image' => '4k-horiz-logo-fires.jpg',
    ]);

    $this->host = User::factory()->create([
        'given_name' => 'Hannah',
        'family_name' => 'Host',
        'email' => 'hannah.host@example.test',
    ]);

    $this->event = Event::factory()->for($this->host)->create([
        'event_type_id' => $this->eventType->id,
        'name' => 'Bozeman Men',
        'city' => 'Bozeman',
    ]);
});

/**
 * @return array<int, array<string, mixed>>
 */
function sentSlackBlocks(): array
{
    $blocks = null;

    SlackAlert::expectMessagesSent(function (array $message) use (&$blocks): bool {
        $blocks = $message['blocks'];

        return true;
    });

    return $blocks;
}

/**
 * @param  array<int, array<string, mixed>>  $blocks
 * @return array<int, array<string, mixed>>
 */
function blocksOfType(array $blocks, string $type): array
{
    return array_values(array_filter($blocks, fn (array $block): bool => $block['type'] === $type));
}

it('posts a new event alert with details, image and buttons', function () {
    (new SendNewEventSlackAlert($this->event))->handle();

    $blocks = sentSlackBlocks();
    $json = json_encode($blocks);

    expect($blocks[0]['text']['text'])->toBe('[TEST] :tada: New Fireside Gathering')
        ->and($blocks[1])->toMatchArray([
            'type' => 'image',
            'image_url' => 'https://fires.b-cdn.net/4k-horiz-logo-fires.jpg?width=600&quality=80&format=jpg',
            'alt_text' => 'Fireside Gatherings',
        ])
        ->and($json)->toContain('Bozeman Men')
        ->and($json)->toContain('hannah.host@example.test')
        ->and($json)->toContain('*Location:*');

    $buttons = blocksOfType($blocks, 'actions')[0]['elements'];

    expect($buttons)->toHaveCount(2)
        ->and($buttons[0])->toMatchArray([
            'action_id' => 'view_admin',
            'url' => $this->event->admin_url,
            'style' => 'primary',
        ])
        ->and($buttons[1])->toMatchArray([
            'action_id' => 'view_public',
            'url' => route('view.event', $this->event),
        ]);
});

it('posts a connect with host message with an image accessory', function () {
    $attendee = Attendee::factory()->for($this->event)->create([
        'given_name' => 'Adam',
        'family_name' => 'Attendee',
        'city' => 'denver',
        'country' => 'US',
    ]);

    (new PostAttendeeContactToSlackJob($attendee, $this->event))->handle();

    $blocks = sentSlackBlocks();
    $sentence = blocksOfType($blocks, 'section')[0];

    expect($blocks[0]['text']['text'])->toBe('[TEST] :wave: Connect with Host request')
        ->and($sentence['text']['text'])->toStartWith('*Adam Attendee* (Denver, ')
        ->and($sentence['text']['text'])->toContain('wants to connect with *Hannah Host* about their Fireside Gathering in Bozeman')
        ->and($sentence['text']['text'])->toEndWith('.')
        ->and($sentence['text']['text'])->not->toContain(' !')
        ->and($sentence['accessory'])->toMatchArray([
            'type' => 'image',
            'image_url' => 'https://fires.b-cdn.net/4k-horiz-logo-fires.jpg?width=300&quality=80&format=jpg',
        ]);
});

it('posts an interest message that differs from the connect message', function () {
    $user = User::factory()->create([
        'given_name' => 'Ivan',
        'family_name' => 'Interested',
        'city' => null,
    ]);

    (new PostUserInterestToSlackJob($user, $this->event))->handle();

    $blocks = sentSlackBlocks();
    $sentence = blocksOfType($blocks, 'section')[0]['text']['text'];

    expect($blocks[0]['text']['text'])->toBe('[TEST] :raised_hand: Interest registered')
        ->and($sentence)->toBe("*Ivan Interested* registered interest in *Hannah Host*'s Fireside Gathering in {$this->event->location}.");
});

it('leaves out the location when the event has no city', function () {
    $this->event->update(['city' => null]);

    $user = User::factory()->create(['given_name' => 'Ivan', 'family_name' => 'Interested', 'city' => null]);

    (new PostUserInterestToSlackJob($user, $this->event->fresh()))->handle();

    $sentence = blocksOfType(sentSlackBlocks(), 'section')[0]['text']['text'];

    expect($sentence)->toBe("*Ivan Interested* registered interest in *Hannah Host*'s Fireside Gathering.");
});

it('does not add an image when the event type has none', function () {
    $this->eventType->update(['new_image' => null]);

    (new SendNewEventSlackAlert($this->event->fresh()))->handle();

    $blocks = sentSlackBlocks();

    expect(blocksOfType($blocks, 'image'))->toBeEmpty()
        ->and(json_encode($blocks))->not->toContain('accessory');
});

it('escapes slack control characters in names', function () {
    $attendee = Attendee::factory()->for($this->event)->create([
        'given_name' => '<!channel>',
        'family_name' => 'Smith & Sons',
    ]);

    (new PostAttendeeContactToSlackJob($attendee, $this->event))->handle();

    $sentence = blocksOfType(sentSlackBlocks(), 'section')[0]['text']['text'];

    expect($sentence)->toStartWith('*&lt;!channel&gt; Smith &amp; Sons*');
});

it('drops the test prefix in production', function () {
    app()->detectEnvironment(fn (): string => 'production');

    (new SendNewEventSlackAlert($this->event))->handle();

    expect(sentSlackBlocks()[0]['text']['text'])->toBe(':tada: New Fireside Gathering');
});

it('sends nothing when slack alerts are disabled', function () {
    config(['slack-alerts.slack_alerts_enabled' => false]);

    (new SendNewEventSlackAlert($this->event))->handle();

    SlackAlert::expectNoMessagesSent();
});
