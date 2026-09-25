<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('s3');

    $this->user = User::factory()->create([
        'provider_id' => 'auth0|photo-user',
        'image' => null,
    ]);

    mockJwtAuthentication($this->user);

    $this->withHeaders(jwtAuthenticationHeaders());
});

it('uploads a profile photo and returns the user payload', function () {
    $response = $this->post('/api/v1/users/me/photo', [
        'photo' => UploadedFile::fake()->image('me.jpg'),
    ], ['Accept' => 'application/json']);

    $this->user->refresh();

    $response->assertOk()
        ->assertJsonPath('id', $this->user->id)
        ->assertJsonPath('image', $this->user->profilePhoto())
        ->assertJsonStructure([
            'id', 'given_name', 'family_name', 'fullname', 'email', 'opted_into_marketing',
            'image', 'profile_step', 'phone', 'city', 'state', 'postal_code',
        ]);

    expect($this->user->image)->toStartWith('users/');

    Storage::disk('s3')->assertExists($this->user->image);
});

it('replaces an existing photo and removes the old file', function () {
    $this->post('/api/v1/users/me/photo', [
        'photo' => UploadedFile::fake()->image('first.png'),
    ], ['Accept' => 'application/json'])->assertOk();

    $firstImage = $this->user->refresh()->image;

    $this->post('/api/v1/users/me/photo', [
        'photo' => UploadedFile::fake()->image('second.webp'),
    ], ['Accept' => 'application/json'])->assertOk();

    $secondImage = $this->user->refresh()->image;

    expect($secondImage)->not->toBe($firstImage);

    Storage::disk('s3')->assertMissing($firstImage);
    Storage::disk('s3')->assertExists($secondImage);
});

it('rejects invalid photo uploads', function (?UploadedFile $photo) {
    $this->post('/api/v1/users/me/photo', array_filter(['photo' => $photo]), ['Accept' => 'application/json'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['photo']);
})->with([
    'missing' => fn () => null,
    'not an image' => fn () => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
    'unsupported image type' => fn () => UploadedFile::fake()->image('me.bmp'),
    'larger than 5MB' => fn () => UploadedFile::fake()->image('big.jpg')->size(5121),
]);

it('deletes the profile photo and returns the user payload', function () {
    Storage::disk('s3')->put('users/existing.jpg', 'image');
    $this->user->forceFill(['image' => 'users/existing.jpg'])->save();

    $this->deleteJson('/api/v1/users/me/photo')
        ->assertOk()
        ->assertJsonPath('id', $this->user->id)
        ->assertJsonStructure(['image']);

    expect($this->user->refresh()->image)->toBeNull();

    Storage::disk('s3')->assertMissing('users/existing.jpg');
});

it('deletes gracefully when the user has no photo', function () {
    $this->deleteJson('/api/v1/users/me/photo')->assertOk();
});

it('requires authentication', function () {
    $this->withHeaders(['Authorization' => ''])
        ->deleteJson('/api/v1/users/me/photo')
        ->assertUnauthorized();

    $this->withHeaders(['Authorization' => ''])
        ->postJson('/api/v1/users/me/photo')
        ->assertUnauthorized();
});
