<?php

use App\Models\User;
use Statamic\Facades\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Statamic\Auth\Eloquent\RoleModel;

beforeEach(function () {
    Queue::fake();
});

it('allows a user with a statamic role to access the control panel', function () {
    $user = User::factory()->create(['super' => false]);

    DB::table('role_user')->insert([
        'user_id' => $user->id,
        'role_id' => 'blog_editor',
    ]);

    $this->actingAs($user)
        ->get('/cp')
        ->assertRedirect('/cp/collections/pages');

    $this->actingAs($user)
        ->get('/cp/collections/blogs')
        ->assertOk();
});

it('denies control panel access to a user without a statamic role', function () {
    $user = User::factory()->create(['super' => false]);

    $this->actingAs($user)
        ->get('/cp')
        ->assertRedirect(route('login'));
});

it('allows a super admin to access the control panel without a role', function () {
    $user = User::factory()->create(['super' => true]);

    $this->actingAs($user)
        ->get('/cp')
        ->assertRedirect('/cp/collections/pages');

    $this->actingAs($user)
        ->get('/cp/collections/pages')
        ->assertOk();
});

it('resolves role permissions from the database', function () {
    $role = Role::find('blog_editor');

    expect($role)->not->toBeNull()
        ->and($role->title())->toBe('Blog Editor')
        ->and($role->permissions()->all())->toContain('access cp');
});

it('seeds roles via migration', function () {
    $roles = RoleModel::all();

    expect($roles)->toHaveCount(2)
        ->and($roles->pluck('handle')->all())->toBe(['blog_editor', 'content_editor']);
});

it('uses the statamic_roles table instead of yaml', function () {
    expect(config('statamic.users.tables.roles'))->toBe('statamic_roles');
});
