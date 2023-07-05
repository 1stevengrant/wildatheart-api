<?php

use App\Actions\Address\CreateAddress;
use App\Http\Livewire\UpdateAddress;
use App\Jobs\PostToZapier;
use App\Models\Campfire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->campfire = Campfire::factory()->create();

    CreateAddress::run([
        'first_name' => 'Steven',
        'last_name' => 'Grant',
        'zip' => '90210',
        'campfire_id' => $this->campfire->id,
    ]);
});

it('updates the address for a fire', function () {
    Queue::fake();
    livewire(UpdateAddress::class, ['campfire' => $this->campfire])
        ->set('street_address', '123 Main St')
        ->set('address2', 'Apt 1')
        ->set('company', 'Tighten')
        ->call('handle');

    $this->assertEquals('123 Main St', $this->campfire->fresh()->address->street_address);
    $this->assertEquals('Apt 1', $this->campfire->fresh()->address->address2);
    $this->assertEquals('Tighten', $this->campfire->fresh()->address->company);

    Queue::assertPushed(PostToZapier::class);
});

it('needs a street address', function () {
    livewire(UpdateAddress::class, ['campfire' => $this->campfire])
        ->set('street_address', '')
        ->call('handle')
        ->assertHasErrors(['street_address' => 'required']);
});

test('street address cannot exceed 255 characters', function () {

    livewire(UpdateAddress::class, ['campfire' => $this->campfire])
        ->set('street_address', str_repeat('a', 256))
        ->call('handle')
        ->assertHasErrors(['street_address' => 'max:255']);
});

test('street address cannot be less than 10 characters', function () {

    livewire(UpdateAddress::class, ['campfire' => $this->campfire])
        ->set('street_address', str_repeat('a', 9))
        ->call('handle')
        ->assertHasErrors(['street_address' => 'min:10']);
});
