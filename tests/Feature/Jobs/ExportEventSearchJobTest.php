<?php

use App\Models\User;
use App\Models\Event;
use App\Jobs\ExportEventSearchJob;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Event as EventFacade;

beforeEach(function () {
    Storage::fake('local');

    $this->user = User::factory()->create();

    EventFacade::listen(MessageSent::class, function (MessageSent $event) {
        $this->csv = $event->message->getAttachments()[0]->getBody();
    });
});

function runEventSearchExport(User $user, bool $includeDeleted = false): void
{
    ExportEventSearchJob::dispatchSync(
        user: $user,
        lat: null,
        lng: null,
        radius: null,
        eventTypeIds: [],
        fields: ['name', 'status', 'not_a_field'],
        activeOnly: true,
        includeDeleted: $includeDeleted,
    );
}

/** @return array<int, array<int, string>> */
function csvRows(string $csv): array
{
    return array_map(
        fn (string $line) => str_getcsv($line, ',', '"', ''),
        array_filter(explode("\n", $csv))
    );
}

it('emails a csv of the matching events in id order', function () {
    $first = Event::factory()->approved()->create(['name' => 'First fire']);
    $second = Event::factory()->approved()->create(['name' => '=Second fire']);
    Event::factory()->create(['name' => 'Unpublished fire']);

    runEventSearchExport($this->user);

    expect(csvRows($this->csv))->toBe([
        ['Event Name', 'Status'],
        [$first->name, 'Published'],
        ["'=Second fire", 'Published'],
    ]);
});

it('includes deleted events when requested', function () {
    Event::factory()->approved()->create(['name' => 'Live fire']);
    Event::factory()->approved()->create(['name' => 'Old fire'])->delete();

    runEventSearchExport($this->user, includeDeleted: true);

    expect(csvRows($this->csv))->toBe([
        ['Event Name', 'Status'],
        ['Live fire', 'Published'],
        ['Old fire', 'Deleted'],
    ]);
});

it('removes the generated file after emailing it', function () {
    Event::factory()->approved()->create();

    runEventSearchExport($this->user);

    expect(Storage::disk('local')->files('exports'))->toBeEmpty();
});
