<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertSoftDeleted;

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    Permission::firstOrCreate(['name' => 'users.view', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'users.create', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'users.update', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'users.delete', 'guard_name' => 'web']);
});

it('can display users index page', function () {
    $actor = User::factory()->create();
    $actor->givePermissionTo(['users.view']);

    actingAs($actor)
        ->get(route('users.index'))
        ->assertStatus(200);
});

it('requires permission to view users', function () {
    actingAs(User::factory()->create())
        ->get(route('users.index'))
        ->assertStatus(403);
});

it('can create a new user', function () {
    $actor = User::factory()->create();
    $actor->givePermissionTo(['users.view', 'users.create']);

    $role = Role::create(['name' => 'Editor', 'guard_name' => 'web']);

    actingAs($actor)
        ->post(route('users.store'), [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'role_id' => $role->id,
        ])
        ->assertRedirect(route('users.index'));

    assertDatabaseHas('users', ['email' => 'jane@example.com']);

    $created = User::where('email', 'jane@example.com')->first();
    expect($created->hasRole($role))->toBeTrue();
});

it('can update an existing user without changing password', function () {
    $actor = User::factory()->create();
    $actor->givePermissionTo(['users.view', 'users.update']);

    $role = Role::create(['name' => 'Viewer', 'guard_name' => 'web']);
    $target = User::factory()->create(['email' => 'old@example.com']);
    $originalHash = $target->password;

    actingAs($actor)
        ->put(route('users.update', $target), [
            'name' => 'Updated Name',
            'email' => 'new@example.com',
            'password' => '',
            'password_confirmation' => '',
            'role_id' => $role->id,
        ])
        ->assertRedirect(route('users.index'));

    assertDatabaseHas('users', ['id' => $target->id, 'email' => 'new@example.com', 'name' => 'Updated Name']);
    expect($target->fresh()->password)->toBe($originalHash);
});

it('can update an existing user with a new password', function () {
    $actor = User::factory()->create();
    $actor->givePermissionTo(['users.view', 'users.update']);

    $role = Role::create(['name' => 'Member', 'guard_name' => 'web']);
    $target = User::factory()->create();
    $oldHash = $target->password;

    actingAs($actor)
        ->put(route('users.update', $target), [
            'name' => $target->name,
            'email' => $target->email,
            'password' => 'NewPassword1!',
            'password_confirmation' => 'NewPassword1!',
            'role_id' => $role->id,
        ])
        ->assertRedirect(route('users.index'));

    expect($target->fresh()->password)->not->toBe($oldHash);
});

it('can delete a user', function () {
    $actor = User::factory()->create();
    $actor->givePermissionTo(['users.view', 'users.delete']);

    $target = User::factory()->create();

    actingAs($actor)
        ->delete(route('users.destroy', $target))
        ->assertRedirect(route('users.index'));

    assertSoftDeleted('users', ['id' => $target->id]);
});

it('requires unique email on store', function () {
    $actor = User::factory()->create();
    $actor->givePermissionTo(['users.view', 'users.create']);

    $role = Role::create(['name' => 'Tester', 'guard_name' => 'web']);
    User::factory()->create(['email' => 'taken@example.com']);

    actingAs($actor)
        ->post(route('users.store'), [
            'name' => 'Duplicate',
            'email' => 'taken@example.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'role_id' => $role->id,
        ])
        ->assertSessionHasErrors('email');
});
