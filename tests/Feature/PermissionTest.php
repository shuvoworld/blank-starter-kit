<?php

use App\Models\Permission;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    Permission::firstOrCreate(['name' => 'permissions.view', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'permissions.create', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'permissions.update', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'permissions.delete', 'guard_name' => 'web']);
});

it('can display permissions index page', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('permissions.view');

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
    $user->givePermissionTo(['permissions.view', 'permissions.create']);

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
    $user->givePermissionTo(['permissions.view', 'permissions.create']);

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
    $user->givePermissionTo(['permissions.view', 'permissions.update']);

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
    $user->givePermissionTo(['permissions.view', 'permissions.delete']);

    $permission = Permission::create(['name' => 'temporary permission', 'guard_name' => 'web']);

    actingAs($user)
        ->delete(route('permissions.destroy', $permission))
        ->assertRedirect(route('permissions.index'));

    expect(Permission::where('name', 'temporary permission')->exists())->toBeFalse();
});

it('cannot delete permission assigned to roles', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(['permissions.view', 'permissions.delete']);

    $permission = Permission::create(['name' => 'protected permission', 'guard_name' => 'web']);
    $role = \App\Models\Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
    $role->givePermissionTo($permission);

    actingAs($user)
        ->delete(route('permissions.destroy', $permission))
        ->assertStatus(422);

    expect(Permission::where('name', 'protected permission')->exists())->toBeTrue();
});

it('can filter permissions by module', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('permissions.view');

    Permission::create(['name' => 'view posts', 'module' => 'posts', 'guard_name' => 'web']);
    Permission::create(['name' => 'view users', 'module' => 'users', 'guard_name' => 'web']);

    $response = actingAs($user)
        ->getJson(route('permissions.by-module', ['module' => 'posts']));

    $response->assertStatus(200);
    $names = collect($response->json('permissions'))->pluck('name');
    expect($names)->toContain('view posts')
        ->not->toContain('view users');
});

it('can search permissions via datatable', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('permissions.view');

    Permission::create(['name' => 'create posts', 'module' => 'posts', 'guard_name' => 'web']);
    Permission::create(['name' => 'delete posts', 'module' => 'posts', 'guard_name' => 'web']);
    Permission::create(['name' => 'edit users', 'module' => 'users', 'guard_name' => 'web']);

    actingAs($user)
        ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
        ->getJson(route('permissions.datatable', [
            'draw' => 1,
            'columns' => [
                ['data' => 'name_badge', 'name' => 'name', 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'module_badge', 'name' => 'module', 'searchable' => 'false', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'description', 'name' => 'description', 'searchable' => 'true', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'roles_count', 'name' => 'roles_count', 'searchable' => 'false', 'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']],
                ['data' => 'action', 'name' => 'action', 'searchable' => 'false', 'orderable' => 'false', 'search' => ['value' => '', 'regex' => 'false']],
            ],
            'order' => [['column' => 0, 'dir' => 'asc']],
            'start' => 0,
            'length' => 100,
            'search' => ['value' => 'create posts', 'regex' => 'false'],
        ]))
        ->assertStatus(200)
        ->assertJsonFragment(['name' => 'create posts']);
});
