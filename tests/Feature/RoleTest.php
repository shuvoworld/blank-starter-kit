<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    // Reset cached permissions
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    // Create common permissions for tests
    Permission::firstOrCreate(['name' => 'roles.view', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'roles.create', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'roles.update', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'roles.delete', 'guard_name' => 'web']);
});

it('can display roles index page', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('roles.view');

    actingAs($user)
        ->get(route('roles.index'))
        ->assertStatus(200);
});

it('requires permission to view roles', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('roles.index'))
        ->assertStatus(403);
});

it('can create a new role', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(['roles.view', 'roles.create']);

    $permission = Permission::create(['name' => 'edit posts', 'guard_name' => 'web']);

    actingAs($user)
        ->post(route('roles.store'), [
            'name' => 'Editor',
            'guard_name' => 'web',
            'description' => 'Content editor role',
            'permissions' => [$permission->id],
        ])
        ->assertRedirect(route('roles.index'));

    assertDatabaseHas('roles', [
        'name' => 'Editor',
        'description' => 'Content editor role',
    ]);
});

it('can update an existing role', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(['roles.view', 'roles.update']);

    $role = Role::create(['name' => 'Writer', 'guard_name' => 'web']);
    $permission = Permission::create(['name' => 'publish posts', 'guard_name' => 'web']);

    actingAs($user)
        ->put(route('roles.update', $role), [
            'name' => 'Publisher',
            'guard_name' => 'web',
            'description' => 'Can publish content',
            'permissions' => [$permission->id],
        ])
        ->assertRedirect(route('roles.index'));

    expect($role->fresh()->name)->toBe('Publisher');
});

it('can delete a role', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(['roles.view', 'roles.delete']);

    $role = Role::create(['name' => 'Temporary', 'guard_name' => 'web']);

    actingAs($user)
        ->delete(route('roles.destroy', $role))
        ->assertRedirect(route('roles.index'));

    expect(Role::where('name', 'Temporary')->exists())->toBeFalse();
});

it('cannot delete super admin role', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(['roles.view', 'roles.delete']);

    $superuser = Role::firstOrCreate(['name' => 'Superuser', 'guard_name' => 'web']);

    actingAs($user)
        ->delete(route('roles.destroy', $superuser))
        ->assertForbidden();

    expect(Role::where('name', 'Superuser')->exists())->toBeTrue();
});

it('can assign permissions to a role', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(['roles.view', 'roles.update']);

    $role = Role::create(['name' => 'Moderator', 'guard_name' => 'web']);
    $permission1 = Permission::create(['name' => 'moderate comments', 'guard_name' => 'web']);
    $permission2 = Permission::create(['name' => 'approve posts', 'guard_name' => 'web']);

    actingAs($user)
        ->post(route('roles.permissions.assign', $role), [
            'permissions' => [$permission1->id, $permission2->id],
        ])
        ->assertRedirect();

    expect($role->fresh()->permissions)->toHaveCount(2);
});

it('can remove a permission from a role', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(['roles.view', 'roles.update']);

    $role = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
    $permission = Permission::create(['name' => 'delete users', 'guard_name' => 'web']);
    $role->givePermissionTo($permission);

    actingAs($user)
        ->delete(route('roles.permissions.remove', [$role, $permission]))
        ->assertRedirect();

    expect($role->fresh()->hasPermissionTo($permission))->toBeFalse();
});

it('cannot delete role with assigned users', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(['roles.view', 'roles.delete']);

    $role = Role::create(['name' => 'User Role', 'guard_name' => 'web']);
    $user->assignRole($role);

    actingAs($user)
        ->delete(route('roles.destroy', $role))
        ->assertStatus(422);

    expect(Role::where('name', 'User Role')->exists())->toBeTrue();
});
