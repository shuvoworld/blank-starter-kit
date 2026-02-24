<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    // Reset cached permissions
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    // Create common permissions for tests
    Permission::firstOrCreate(['name' => 'view any roles', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'access dashboard', 'guard_name' => 'web']);
});

it('allows access to users with correct role', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'Editor', 'guard_name' => 'web']);
    $user->assignRole($role);

    actingAs($user)
        ->get(route('dashboard'))
        ->assertStatus(200);
});

it('allows access to users with correct permission', function () {
    $user = User::factory()->create();
    $permission = Permission::firstOrCreate(['name' => 'access dashboard', 'guard_name' => 'web']);
    $user->givePermissionTo($permission);

    actingAs($user)
        ->get(route('dashboard'))
        ->assertStatus(200);
});

it('denies access without required role', function () {
    $user = User::factory()->create();

    // Should fail because user doesn't have permission
    actingAs($user)
        ->get(route('roles.index'))
        ->assertStatus(403);
});

it('denies access without required permission', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('roles.index'))
        ->assertStatus(403);
});

it('allows super admin to access all permissions', function () {
    $user = User::factory()->create();
    $superAdminRole = Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);
    $user->assignRole($superAdminRole);

    // Give all permissions to super admin
    $permissions = Permission::pluck('id');
    $superAdminRole->syncPermissions($permissions);

    actingAs($user)
        ->get(route('roles.index'))
        ->assertStatus(200);
});

it('correctly checks has permission on user model', function () {
    $user = User::factory()->create();
    $permission = Permission::create(['name' => 'edit articles', 'guard_name' => 'web']);

    expect($user->hasPermissionTo('edit articles'))->toBeFalse();

    $user->givePermissionTo($permission);

    expect($user->fresh()->hasPermissionTo('edit articles'))->toBeTrue();
});

it('correctly checks has role on user model', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'Journalist', 'guard_name' => 'web']);

    expect($user->hasRole('Journalist'))->toBeFalse();

    $user->assignRole($role);

    expect($user->fresh()->hasRole('Journalist'))->toBeTrue();
});

it('correctly checks has any permission', function () {
    $user = User::factory()->create();
    $permission1 = Permission::create(['name' => 'write posts', 'guard_name' => 'web']);
    $permission2 = Permission::create(['name' => 'edit posts', 'guard_name' => 'web']);

    $user->givePermissionTo($permission1);

    expect($user->hasAnyPermission(['write posts', 'publish posts']))->toBeTrue();
    expect($user->hasAnyPermission(['edit posts', 'publish posts']))->toBeFalse();
});

it('correctly checks has any role', function () {
    $user = User::factory()->create();
    $role1 = Role::create(['name' => 'Writer', 'guard_name' => 'web']);
    $role2 = Role::create(['name' => 'Editor', 'guard_name' => 'web']);

    $user->assignRole($role1);

    expect($user->hasAnyRole(['Writer', 'Publisher']))->toBeTrue();
    expect($user->hasAnyRole(['Editor', 'Publisher']))->toBeFalse();
});

it('user gets permissions through role', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'Content Manager', 'guard_name' => 'web']);
    $permission = Permission::create(['name' => 'manage categories', 'guard_name' => 'web']);

    $role->givePermissionTo($permission);
    $user->assignRole($role);

    expect($user->hasPermissionTo('manage categories'))->toBeTrue();
});

it('user gets all permissions through multiple roles', function () {
    $user = User::factory()->create();
    $role1 = Role::create(['name' => 'Writer', 'guard_name' => 'web']);
    $role2 = Role::create(['name' => 'Editor', 'guard_name' => 'web']);

    $permission1 = Permission::create(['name' => 'write posts', 'guard_name' => 'web']);
    $permission2 = Permission::create(['name' => 'edit posts', 'guard_name' => 'web']);

    $role1->givePermissionTo($permission1);
    $role2->givePermissionTo($permission2);

    $user->assignRole([$role1, $role2]);

    expect($user->hasAllPermissions(['write posts', 'edit posts']))->toBeTrue();
});

it('can sync roles to user', function () {
    $user = User::factory()->create();
    $role1 = Role::create(['name' => 'Role 1', 'guard_name' => 'web']);
    $role2 = Role::create(['name' => 'Role 2', 'guard_name' => 'web']);

    $user->syncRoles([$role1]);

    expect($user->roles)->toHaveCount(1);
    expect($user->hasRole('Role 1'))->toBeTrue();

    $user->syncRoles([$role1, $role2]);

    expect($user->fresh()->roles)->toHaveCount(2);
    expect($user->fresh()->hasAllRoles(['Role 1', 'Role 2']))->toBeTrue();
});

it('can remove role from user', function () {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'Temporary Role', 'guard_name' => 'web']);
    $user->assignRole($role);

    expect($user->hasRole('Temporary Role'))->toBeTrue();

    $user->removeRole($role);

    expect($user->fresh()->hasRole('Temporary Role'))->toBeFalse();
});

it('can sync permissions to role', function () {
    $role = Role::create(['name' => 'Admin', 'guard_name' => 'web']);
    $permission1 = Permission::create(['name' => 'view users', 'guard_name' => 'web']);
    $permission2 = Permission::create(['name' => 'manage users', 'guard_name' => 'web']);

    $role->syncPermissions([$permission1]);

    expect($role->permissions)->toHaveCount(1);
    expect($role->hasPermissionTo('view users'))->toBeTrue();

    $role->syncPermissions([$permission1, $permission2]);

    expect($role->fresh()->permissions)->toHaveCount(2);
});
