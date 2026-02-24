<?php

use App\Models\Permission;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    // Reset cached permissions
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    // Create common permissions for tests
    Permission::firstOrCreate(['name' => 'view any permissions', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'create permissions', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'update permissions', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'delete permissions', 'guard_name' => 'web']);
});

it('can display permissions index page', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('view any permissions');

    actingAs($user)
        ->get(route('permissions.index'))
        ->assertStatus(200);
});

it('requires permission to view permissions', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('permissions.index'))
        ->assertStatus(403);
});

it('can create a new permission', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(['view any permissions', 'create permissions']);

    actingAs($user)
        ->post(route('permissions.store'), [
            'name' => 'edit posts',
            'guard_name' => 'web',
            'module' => 'posts',
            'description' => 'Can edit blog posts',
        ])
        ->assertRedirect(route('permissions.index'));

    assertDatabaseHas('permissions', [
        'name' => 'edit posts',
        'module' => 'posts',
    ]);
});

it('cannot create duplicate permission', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(['view any permissions', 'create permissions']);

    Permission::create(['name' => 'delete posts', 'guard_name' => 'web']);

    actingAs($user)
        ->post(route('permissions.store'), [
            'name' => 'delete posts',
            'guard_name' => 'web',
            'module' => 'posts',
        ])
        ->assertSessionHasErrors('name');
});

it('can update an existing permission', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(['view any permissions', 'update permissions']);

    $permission = Permission::create(['name' => 'old permission', 'guard_name' => 'web']);

    actingAs($user)
        ->put(route('permissions.update', $permission), [
            'name' => 'new permission',
            'guard_name' => 'web',
            'module' => 'users',
            'description' => 'Updated description',
        ])
        ->assertRedirect(route('permissions.index'));

    expect($permission->fresh()->name)->toBe('new permission');
    expect($permission->fresh()->module)->toBe('users');
});

it('can delete a permission', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(['view any permissions', 'delete permissions']);

    $permission = Permission::create(['name' => 'temporary permission', 'guard_name' => 'web']);

    actingAs($user)
        ->delete(route('permissions.destroy', $permission))
        ->assertRedirect(route('permissions.index'));

    expect(Permission::where('name', 'temporary permission')->exists())->toBeFalse();
});

it('cannot delete permission assigned to roles', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(['view any permissions', 'delete permissions']);

    $permission = Permission::create(['name' => 'protected permission', 'guard_name' => 'web']);
    $role = \App\Models\Role::create(['name' => 'Admin', 'guard_name' => 'web']);
    $role->givePermissionTo($permission);

    actingAs($user)
        ->delete(route('permissions.destroy', $permission))
        ->assertRedirect();

    expect(Permission::where('name', 'protected permission')->exists())->toBeTrue();
});

it('can filter permissions by module', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('view any permissions');

    Permission::create(['name' => 'view posts', 'module' => 'posts', 'guard_name' => 'web']);
    Permission::create(['name' => 'view users', 'module' => 'users', 'guard_name' => 'web']);

    actingAs($user)
        ->get(route('permissions.index', ['module' => 'posts']))
        ->assertStatus(200)
        ->assertSee('view posts');
});

it('can search permissions', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('view any permissions');

    Permission::create(['name' => 'create posts', 'module' => 'posts', 'guard_name' => 'web']);
    Permission::create(['name' => 'delete posts', 'module' => 'posts', 'guard_name' => 'web']);
    Permission::create(['name' => 'edit users', 'module' => 'users', 'guard_name' => 'web']);

    actingAs($user)
        ->get(route('permissions.index', ['search' => 'posts']))
        ->assertStatus(200)
        ->assertSee('create posts')
        ->assertSee('delete posts')
        ->assertDontSee('edit users');
});
