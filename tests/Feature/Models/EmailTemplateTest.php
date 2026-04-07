<?php

use App\Models\User;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    Bus::fake();
    Mail::fake();
});

it('returns associated user when lastUpdatedByUser is called', function () {
    $user = User::factory()->create();
    $emailTemplate = EmailTemplate::factory()->create(['last_updated_by' => $user->id]);

    expect($user->id)->toBe($emailTemplate->lastUpdatedByUser->id);
});

it('returns null when lastUpdatedByUser is called and no associated user exists', function () {
    $emailTemplate = EmailTemplate::factory()->create(['last_updated_by' => null]);

    expect($emailTemplate->lastUpdatedByUser)->toBeNull();
});

it('creates slug from name when email template is created', function () {
    $emailTemplate = EmailTemplate::factory()->make(['name' => 'Test Template']);

    $emailTemplate->save();

    assertDatabaseHas('email_templates', [
        'id' => $emailTemplate->id,
        'slug' => Str::slug($emailTemplate->name),
    ]);
});

it('updates slug and last_updated_by when email template is updated', function () {
    $user = User::factory()->create();
    Auth::login($user);

    $emailTemplate = EmailTemplate::factory()->create(['name' => 'Test Template']);
    $emailTemplate->name = 'Updated Template';

    $emailTemplate->save();

    assertDatabaseHas('email_templates', [
        'id' => $emailTemplate->id,
        'slug' => Str::slug($emailTemplate->name),
        'last_updated_by' => $user->id,
    ]);
});

it('casts is_active attribute to boolean when true', function () {
    $emailTemplate = EmailTemplate::factory()->create(['is_active' => 1]);

    expect($emailTemplate->is_active)->toBeTrue();
});

it('casts is_active attribute to boolean when false', function () {
    $emailTemplate = EmailTemplate::factory()->create(['is_active' => 0]);

    expect($emailTemplate->is_active)->toBeFalse();
});

it('casts is_active attribute to boolean when set to truthy value', function () {
    $emailTemplate = EmailTemplate::factory()->create(['is_active' => 1]);

    expect($emailTemplate->is_active)->toBeTrue();
});

it('casts is_active attribute to boolean when set to falsy value', function () {
    $emailTemplate = EmailTemplate::factory()->create(['is_active' => 0]);

    expect($emailTemplate->is_active)->toBeFalse();
});

it('defaults is_active attribute to null when not set', function () {
    $emailTemplate = EmailTemplate::factory()->create();
    expect($emailTemplate->is_active)->toBeNull();
});
