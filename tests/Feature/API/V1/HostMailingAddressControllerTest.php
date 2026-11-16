<?php

use App\Models\User;
use App\Models\Address;

beforeEach(function () {
    $this->host = User::factory()->create(['given_name' => null, 'family_name' => null]);

    mockJwtAuthentication($this->host);

    $this->addressData = [
        'given_name' => 'John',
        'family_name' => 'Doe',
        'street_address' => '123 Main St',
        'address2' => 'Suite 100',
        'company' => 'Test Company',
        'city' => 'Anytown',
        'state' => 'CA',
        'zip' => '12345',
        'country' => 'US',
        'phone' => '1234567890',
    ];
});

it('replaces the host\'s primary mailing address', function () {
    $previous = Address::factory()->for($this->host)->create(['is_primary' => true]);

    $response = $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson('/api/v1/host/mailing-address', $this->addressData)
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('address.street_address', '123 Main St')
        ->assertJsonPath('address.is_primary', true);

    expect($previous->fresh()->is_primary)->toBeFalse()
        ->and($this->host->addresses()->where('is_primary', true)->sole()->id)->toBe($response->json('address.id'));
});

it('fills in the host\'s missing name', function () {
    $this->withHeaders(jwtAuthenticationHeaders())
        ->postJson('/api/v1/host/mailing-address', $this->addressData)
        ->assertOk();

    expect($this->host->fresh())
        ->given_name->toBe('John')
        ->family_name->toBe('Doe')
        ->name->toBe('John Doe');
});
