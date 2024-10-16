<?php

use App\Models\User;

beforeEach(function () {
    $this->seed();
});
test('only a super admin can access the internal reporting url', function () {
    $user = User::factory()->create(['super' => false]);
    $response = $this->actingAs($user)->get(route('internal.reporting'));
    expect($response->status())->toBe(403);

    $user->super = true;
    $user->save();
    $response = $this->actingAs($user)->get(route('internal.reporting'));
    expect($response->status())->toBe(200);
});
