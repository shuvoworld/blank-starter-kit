# Module Development Handbook

A complete reference for building, customising, and securing CRUD modules in this project.

---

## Table of Contents

- [Quick Start Checklist](#quick-start-checklist)
- [Architecture Overview](#architecture-overview)
- [Generated File Structure](#generated-file-structure)
- [Step-by-Step Walkthrough](#step-by-step-walkthrough)
  - [1. Scaffold the Module](#1-scaffold-the-module)
  - [2. Define the Migration](#2-define-the-migration)
  - [3. Update the Model](#3-update-the-model)
  - [4. Add Validation Rules](#4-add-validation-rules)
  - [5. Register the Route](#5-register-the-route)
  - [6. Assign Permissions to Roles](#6-assign-permissions-to-roles)
  - [7. Add a Sidebar Link](#7-add-a-sidebar-link)
  - [8. Customise the Views](#8-customise-the-views)
- [Permission & Authorization System](#permission--authorization-system)
  - [How It All Fits Together](#how-it-all-fits-together)
  - [Permission Naming Convention](#permission-naming-convention)
  - [Layer 0 — Global Superuser Bypass](#layer-0--global-superuser-bypass)
  - [Layer 1 — Route Middleware](#layer-1--route-middleware)
  - [Layer 2 — Database Permissions (Spatie)](#layer-2--database-permissions-spatie)
  - [Layer 3 — Policy](#layer-3--policy)
  - [Layer 4 — Controller Guards](#layer-4--controller-guards)
  - [Layer 5 — DataTable Query Scoping](#layer-5--datatable-query-scoping)
  - [Layer 6 — Blade Views](#layer-6--blade-views)
- [Policy Deep Dive](#policy-deep-dive)
  - [What BasePolicy Gives You for Free](#what-basepolicy-gives-you-for-free)
  - [When to Override a Policy Method](#when-to-override-a-policy-method)
  - [When to Add a New Policy Method](#when-to-add-a-new-policy-method)
  - [Policy Decision Tree](#policy-decision-tree)
  - [Custom Permissions (Beyond CRUD)](#custom-permissions-beyond-crud)
- [Where to Put What — Decision Guide](#where-to-put-what--decision-guide)
  - [Route Middleware vs Policy vs Controller Guard](#route-middleware-vs-policy-vs-controller-guard)
  - [@can vs $user->can() vs abort_if()](#can-vs-usercan-vs-abort_if)
  - [Ownership Checks](#ownership-checks)
- [Service Layer](#service-layer)
  - [When to Use a Service](#when-to-use-a-service)
  - [Available Hooks](#available-hooks)
  - [Important: Hook Limitations](#important-hook-limitations)
- [Writing Tests](#writing-tests)
  - [Test File Location and Naming](#test-file-location-and-naming)
  - [beforeEach Setup](#beforeeach-setup)
  - [What to Test](#what-to-test)
  - [Test Examples](#test-examples)
- [Removing a Module](#removing-a-module)
- [Reference](#reference)
  - [BaseController Hooks](#basecontroller-hooks)
  - [BaseFormRequest](#baseformrequest)
  - [Observer](#observer)
  - [DataTable Controller](#datatable-controller)
  - [Form Input Components](#form-input-components)
  - [Reusable File Input](#reusable-file-input)
  - [Select2 Dropdowns](#select2-dropdowns)
  - [JavaScript Timing Pattern](#javascript-timing-pattern)
- [Seeded Permissions Reference](#seeded-permissions-reference)
- [Common Mistakes](#common-mistakes)
- [Customising Stubs](#customising-stubs)

---

## Quick Start Checklist

```
[ ] 1. php artisan make:crud-module YourModuleName
[ ] 2. Add columns to the migration → php artisan migrate
       (4 permissions are auto-created: module.view / create / update / delete)
[ ] 3. Add columns to $fillable (and casts) in the Model
[ ] 4. Add validation rules to YourModuleRequest
[ ] 5. Add Route::crudModule(...) to routes/web.php
[ ] 6. Assign permissions to roles (via Roles UI or seeder)
[ ] 7. Add a sidebar link
[ ] 8. Customise views (form fields, table columns, show page)
[ ] 9. Write feature tests (tests/Feature/YourModuleTest.php)
```

---

## Architecture Overview

Every module is built on three base classes:

| Class | Purpose |
|---|---|
| `BaseController` | All 7 CRUD actions with authorization, validation, flash messages, and file handling |
| `BaseDataTableController` | Server-side DataTables AJAX endpoint |
| `BasePolicy` | Superuser bypass + default CRUD permission checks |

```
YourModuleController          → extends BaseController
YourModuleDataTableController → extends BaseDataTableController
YourModulePolicy              → extends BasePolicy
YourModuleService             → extends BaseService   (optional)
```

Authorization flows through layers in this order:

```
[Request]
    ↓
Route Middleware  (rejects if user lacks module.view / .create / .update / .delete)
    ↓
BaseController::authorizeAction()  (calls policy)
    ↓
YourModulePolicy  (checks fine-grained permission via Spatie)
    ↓
Spatie hasPermissionTo()  (checks DB)
```

---

## Generated File Structure

`php artisan make:crud-module PostCategory` generates these **10 files**:

```
database/migrations/
    YYYY_MM_DD_HHMMSS_create_post_categories_table.php   ← schema + creates 4 permissions

app/
    Models/PostCategory.php
    Observers/PostCategoryObserver.php
    Policies/PostCategoryPolicy.php
    Http/
        Requests/PostCategoryRequest.php
        Controllers/
            PostCategoryController.php
            PostCategoryDataTableController.php

resources/views/post-categories/
    index.blade.php
    form.blade.php
    show.blade.php
```

Nothing else to create. All wiring (service container, policy auto-discovery, observer registration) is handled automatically by Laravel conventions.

---

## Step-by-Step Walkthrough

### 1. Scaffold the Module

```bash
php artisan make:crud-module PostCategory
```

The name must be **PascalCase**, singular. The generator derives all other forms automatically:

| Token | Example |
|---|---|
| `{ModuleName}` | `PostCategory` |
| `{moduleName}` | `postCategory` |
| `{Module Name}` | `Post Category` |
| `{module name}` | `post category` |
| `{module-names}` | `post-categories` |
| `{module_names}` | `post_categories` |
| `{Module Names}` | `Post Categories` |

Use `--force` to overwrite existing files.

---

### 2. Define the Migration

Open the generated migration. The stub already creates `id`, `name`, `timestamps`, `created_by`, `updated_by`, `softDeletes`, `deleted_by`, and the 4 Spatie permissions. Add your own columns in between:

```php
Schema::create('post_categories', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();           // your columns
    $table->text('description')->nullable();    // your columns
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->unsignedBigInteger('created_by')->nullable();
    $table->unsignedBigInteger('updated_by')->nullable();
    $table->softDeletes();
    $table->unsignedBigInteger('deleted_by')->nullable();
});
```

The migration's `up()` automatically creates four permissions after creating the table:

```
post-categories.view
post-categories.create
post-categories.update
post-categories.delete
```

These match the route middleware generated by `Route::crudModule()`. **Do not rename or remove them** — the route middleware, policies, and role assignments all depend on this naming.

```bash
php artisan migrate
```

---

### 3. Update the Model

Add your columns to `$fillable`. Add `casts()` for typed fields:

```php
protected $fillable = [
    'name',
    'slug',
    'description',
    'is_active',
    'created_by',
    'updated_by',
    'deleted_by',
];

protected function casts(): array
{
    return [
        'is_active' => 'boolean',
    ];
}
```

The stub already includes `SoftDeletes`, `HasActivityLog`, `HasFactory`, and the Observer registration. Do not remove these.

**Adding relationships** — add them as typed methods below `$fillable`:

```php
public function posts(): HasMany
{
    return $this->hasMany(Post::class, 'category_id');
}
```

---

### 4. Add Validation Rules

`PostCategoryRequest` extends `BaseFormRequest`. Use `recordId()` to make unique rules ignore the record being updated:

```php
public function rules(): array
{
    $id = $this->recordId(); // null on create, the record ID on update

    return [
        'name'        => ['required', 'string', 'max:255', Rule::unique('post_categories')->ignore($id)],
        'slug'        => ['required', 'string', 'max:255', Rule::unique('post_categories')->ignore($id)],
        'description' => ['nullable', 'string'],
        'is_active'   => ['boolean'],
    ];
}
```

`isUpdating()` returns `true` when the request is for an update. Use it to make fields conditionally required:

```php
'password' => $this->isUpdating()
    ? ['nullable', 'confirmed', Password::defaults()]
    : ['required', 'confirmed', Password::defaults()],
```

---

### 5. Register the Route

Inside the `auth` middleware group in `routes/web.php`:

```php
// Minimal — standard CRUD + datatable
Route::crudModule('post-categories', PostCategoryController::class, PostCategoryDataTableController::class);

// With extra routes — pass a closure as the 4th argument
// Extra static routes must be defined BEFORE {record} wildcards (handled automatically by the macro)
Route::crudModule('post-categories', PostCategoryController::class, PostCategoryDataTableController::class, function () {
    Route::post('/{record}/publish', [PostCategoryController::class, 'publish'])
        ->name('publish')
        ->middleware('permission:post-categories.update');
});
```

`Route::crudModule('post-categories', ...)` generates these 8 named routes:

| Method | URI | Name | Middleware |
|---|---|---|---|
| GET | `/post-categories/datatable` | `post-categories.datatable` | `permission:post-categories.view` |
| GET | `/post-categories` | `post-categories.index` | `permission:post-categories.view` |
| GET | `/post-categories/create` | `post-categories.create` | `post-categories.view` + `.create` |
| POST | `/post-categories` | `post-categories.store` | `post-categories.view` + `.create` |
| GET | `/post-categories/{record}` | `post-categories.show` | `permission:post-categories.view` |
| GET | `/post-categories/{record}/edit` | `post-categories.edit` | `post-categories.view` + `.update` |
| PUT | `/post-categories/{record}` | `post-categories.update` | `post-categories.view` + `.update` |
| DELETE | `/post-categories/{record}` | `post-categories.destroy` | `post-categories.view` + `.delete` |

> **Important:** Any extra static routes (e.g. `/post-categories/export`) are registered before the `/{record}` wildcard by the macro, so they are not accidentally matched as record IDs.

---

### 6. Assign Permissions to Roles

**Via the UI:** Go to Roles → edit a role → assign permissions.

**Via a seeder** (preferred for a consistent initial state):

```php
// In RbacSeeder or a dedicated seeder
$adminRole = Role::where('name', 'Admin')->first();

$adminRole?->givePermissionTo([
    'post-categories.view',
    'post-categories.create',
    'post-categories.update',
    'post-categories.delete',
]);
```

After adding a seeder, update `EmployeeProductPermissionsSeeder` (or the relevant seeder) following the existing patterns.

---

### 7. Add a Sidebar Link

In `resources/views/layouts/dashboard/partials/sidebar.blade.php`, add a nav item guarded by `@can`:

```blade
@can('post-categories.view')
    <li class="nav-item">
        <a href="{{ route('post-categories.index') }}"
           class="nav-link {{ request()->routeIs('post-categories.*') ? 'active' : '' }}">
            <i class="nav-icon bi bi-tag"></i>
            <p>Post Categories</p>
        </a>
    </li>
@endcan
```

---

### 8. Customise the Views

**`index.blade.php`** — Add/remove table columns in `PostCategoryDataTableController::tableColumns()`. The view itself rarely needs changing.

**`form.blade.php`** — Add form fields using the shared input components (see [Form Input Components](#form-input-components)).

**`show.blade.php`** — Add rows to the detail table.

**`PostCategoryDataTableController`** — Override `indexQuery()` to add eager loading, scopes, or ordering. Override `dataTableColumns()` to add computed columns. Override `actionColumn()` to change the action buttons.

---

## Permission & Authorization System

### How It All Fits Together

Every request for a protected resource goes through this chain:

```
HTTP Request
   ↓
[auth middleware]          — must be logged in
   ↓
[permission:{module}.view] — must have view permission (route middleware)
   ↓
BaseController action      — calls $this->authorizeAction(ability, record)
   ↓
Gate / Policy              — calls YourModulePolicy::{ability}($user, $record)
   ↓
BasePolicy checks          — $user->can('{module}.{ability}') → Spatie DB lookup
```

A request is rejected at the **first layer** that denies it. Superusers (role: `Superuser`) bypass every check via a global `Gate::before` callback.

---

### Permission Naming Convention

**All permissions use dot-notation:** `{kebab-module-name}.{action}`

```
post-categories.view
post-categories.create
post-categories.update
post-categories.delete
```

Never use space-format (`create post categories`) — it is not used anywhere in this codebase. The route middleware, policies, and Blade directives all use dot-notation.

---

### Layer 0 — Global Superuser Bypass

```php
// AppServiceProvider::boot()
Gate::before(function (User $user, string $ability): ?bool {
    if ($user->hasRole('Superuser')) {
        return true;
    }
    return null; // let other checks proceed
});
```

- Fires before **every** `Gate::allows()` / `$user->can()` / `@can` / `authorize()` call.
- Does **not** fire for `$user->hasRole()` or `$user->hasPermissionTo()` called directly.
- The `permission:` route middleware calls `$user->can()`, so Superusers pass route checks too.
- `BasePolicy::before()` is a redundant safety net for the same thing at the policy level.

---

### Layer 1 — Route Middleware

Generated automatically by `Route::crudModule()`. Uses Spatie's `permission:` middleware:

```php
->middleware('permission:post-categories.view')   // index, show, datatable
->middleware('permission:post-categories.create') // create, store
->middleware('permission:post-categories.update') // edit, update
->middleware('permission:post-categories.delete') // destroy
```

For manually-registered routes (not via `crudModule`), add the middleware yourself:

```php
Route::post('/{record}/approve', [Controller::class, 'approve'])
    ->middleware('permission:leave-requests.approve');
```

**Role middleware** (`role:Admin`) also exists but bypasses Superuser. **Avoid it** in new modules — use `permission:` instead.

---

### Layer 2 — Database Permissions (Spatie)

Permissions are rows in the `permissions` table, assigned to roles. The system checks:

```
$user->hasPermissionTo('post-categories.view')
// equivalent to checking if any of the user's roles has this permission
```

Permissions are created in the migration — never hardcode permission strings outside of:
- Migrations (creation)
- Seeders (role assignment)
- Route middleware definitions
- Policy `can()` calls
- Blade `@can` directives

---

### Layer 3 — Policy

Every module has a Policy class. `BaseController` calls `$this->authorizeAction()` automatically in every CRUD action — you never need to call `authorize()` yourself unless adding a custom action.

```php
// BaseController calls these automatically:
index()   → authorizeAction('viewAny')           → Policy::viewAny($user)
create()  → authorizeAction('create')            → Policy::create($user)
store()   → authorizeAction('create')            → Policy::create($user)
show()    → authorizeAction('view', $record)     → Policy::view($user, $record)
edit()    → authorizeAction('update', $record)   → Policy::update($user, $record)
update()  → authorizeAction('update', $record)   → Policy::update($user, $record)
destroy() → authorizeAction('delete', $record)   → Policy::delete($user, $record)
```

For custom actions, call it explicitly in your controller method:

```php
public function publish(int|string $record): RedirectResponse
{
    $postCategory = $this->findRecord($record);
    $this->authorizeAction('update', $postCategory); // reuse 'update' or add 'publish' policy method
    // ...
}
```

---

### Layer 4 — Controller Guards

For business-rule guards that are not permission-based, use `beforeDestroy()`:

```php
protected function beforeDestroy(Model $record): void
{
    abort_if($record->posts()->exists(), 422, 'Cannot delete a category that has posts.');
}
```

For custom action controllers, use `abort_if()` / `abort_unless()` directly:

```php
abort_if(! $record->canBePublished(), 422, 'This category cannot be published.');
```

---

### Layer 5 — DataTable Query Scoping

Restrict what records are visible at the query level. Override `indexQuery()` in the DataTable controller:

```php
protected function indexQuery(): Builder
{
    // Admin sees all; employees see only their own
    if (auth()->user()->can('post-categories.view')) {
        return PostCategory::query();
    }

    return PostCategory::query()->where('created_by', auth()->id());
}
```

---

### Layer 6 — Blade Views

Hide UI elements for users who lack permission. Use the policy-aware `@can` directive:

```blade
{{-- Show "Add" button only if user can create --}}
@can('post-categories.create')
    <a href="{{ route('post-categories.create') }}" class="btn btn-primary">Add</a>
@endcan

{{-- Edit link on the show page --}}
@can('post-categories.update')
    <a href="{{ route('post-categories.edit', $postCategory) }}" class="btn btn-primary">Edit</a>
@endcan

{{-- Checking the model instance (uses Policy::update($user, $record)) --}}
@can('update', $postCategory)
    ...
@endcan
```

`@can('post-categories.update')` and `@can('update', $postCategory)` are **different**:
- `@can('post-categories.update')` — checks the permission string directly (does not invoke policy)
- `@can('update', $postCategory)` — invokes `PostCategoryPolicy::update($user, $postCategory)` (policy-aware, respects ownership rules)

---

## Policy Deep Dive

### What BasePolicy Gives You for Free

```php
abstract class BasePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('Superuser')) {
            return true; // Superusers skip all checks
        }
        return null;
    }

    abstract protected function resource(): string; // e.g. 'post-categories'

    public function viewAny(User $user): bool  { return $user->can("{$this->resource()}.view"); }
    public function view(User $user, Model $model): bool  { return $user->can("{$this->resource()}.view"); }
    public function create(User $user): bool  { return $user->can("{$this->resource()}.create"); }
    public function update(User $user, Model $model): bool  { return $user->can("{$this->resource()}.update"); }
    public function delete(User $user, Model $model): bool  { return $user->can("{$this->resource()}.delete"); }
}
```

The generated policy stub is just this:

```php
class PostCategoryPolicy extends BasePolicy
{
    protected function resource(): string
    {
        return 'post-categories';
    }
}
```

**This is correct and sufficient for most modules.** Do not add code to a policy unless you have a reason from the list below.

---

### When to Override a Policy Method

Override a base method when the default permission check is **not enough** — specifically when access also depends on ownership or model state.

**Ownership example** (`UserPolicy`):
```php
// Users can view/edit their own profile even without users.view permission
public function view(User $user, Model $model): bool
{
    return $user->id === $model->id || $user->can('users.view');
}

public function update(User $user, Model $model): bool
{
    return $user->id === $model->id || $user->can('users.update');
}
```

**State-based example** (`LeaveRequestPolicy`):
```php
// Any logged-in user can see the leave request list (their own, scoped in query)
public function viewAny(User $user): bool
{
    return true; // no permission check; query is scoped in DataTable
}

// Owner can view their own, admins can view all
public function view(User $user, Model $model): bool
{
    return $user->id === $model->user_id || $user->can('leave-requests.view');
}
```

**Rule of thumb:**
- Default: `return $user->can('{module}.{action}')` ← keep as-is (inherited from BasePolicy)
- Override when: ownership matters, OR model state matters, OR permission should be `true` for everyone

---

### When to Add a New Policy Method

Add a method for every **non-standard action** your controller exposes. The method name must match the string passed to `authorizeAction()` or `$this->authorize()`.

```php
// In PostCategoryPolicy
public function publish(User $user, Model $model): bool
{
    return $model->isDraft() && $user->can('post-categories.update');
}

public function archive(User $user, Model $model): bool
{
    return $user->can('post-categories.delete');
}
```

```php
// In the controller
public function publish(int|string $record): RedirectResponse
{
    $category = $this->findRecord($record);
    $this->authorizeAction('publish', $category); // calls PostCategoryPolicy::publish()
    // ...
}
```

If the action uses an **existing permission** but with a state check (e.g. "only update draft records"), override the existing method. If it uses a **new custom permission** (e.g. `post-categories.publish`), create the permission in a migration and add the policy method.

---

### Policy Decision Tree

```
Do you need to protect a CRUD action?
├── YES — Does BasePolicy cover it? (pure permission check, no ownership/state)
│   ├── YES → Leave the policy stub as-is. Nothing to do.
│   └── NO  → Override the method in YourModulePolicy.
│
└── NO — Is it a custom action (approve, publish, export, cancel)?
    ├── Does it map to an EXISTING permission? (e.g. update)
    │   └── Add a policy method that calls $user->can('{module}.update') + state check
    └── Does it need a NEW permission? (e.g. post-categories.publish)
        ├── Create the permission in a new migration
        ├── Add the policy method: $user->can('post-categories.publish')
        └── Add route middleware: permission:post-categories.publish
```

---

### Custom Permissions (Beyond CRUD)

When your module needs actions beyond view/create/update/delete (e.g. approve, reject, export, assign), create them in a separate migration:

```php
// database/migrations/YYYY_MM_DD_add_publish_permission_to_post_categories.php
public function up(): void
{
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    Permission::firstOrCreate(
        ['name' => 'post-categories.publish', 'guard_name' => 'web'],
        ['module' => 'post-categories']
    );
}

public function down(): void
{
    Permission::where('name', 'post-categories.publish')->delete();
}
```

Then add the route middleware and policy method as described above. Assign the new permission to the relevant role in a seeder.

---

## Where to Put What — Decision Guide

### Route Middleware vs Policy vs Controller Guard

| Concern | Where to handle it |
|---|---|
| "Can this user access this module at all?" | Route middleware (`permission:module.action`) |
| "Can this user perform this action on this specific record?" | Policy method |
| "Is this record in a valid state for this action?" | Policy method (state check) OR `abort_if()` in controller |
| "Does the user own this record?" | Policy method (ownership check) |
| "Does this delete violate a business rule?" | `beforeDestroy()` in controller |
| "Should this UI button be visible?" | `@can` in Blade |
| "Should this data even appear in results?" | `indexQuery()` in DataTable controller |

### @can vs $user->can() vs abort_if()

**`@can('permission.string')` in Blade** — show/hide UI elements. Does not protect the server-side action.

```blade
@can('post-categories.create')
    <a href="{{ route('post-categories.create') }}">Add</a>
@endcan
```

**`$user->can('permission.string')` in PHP** — programmatic check in controllers, services, or jobs. Triggers the Gate (and `Gate::before` Superuser bypass):

```php
if (auth()->user()->can('post-categories.export')) {
    // proceed
}
```

**`abort_if()` / `abort_unless()` in controllers** — for business-rule guards that are not permission-based. Does **not** go through the Gate:

```php
abort_if($record->isLocked(), 422, 'This record is locked.');
abort_unless($record->isDraft(), 422, 'Only draft records can be published.');
```

**`$this->authorizeAction()` in controllers** — calls the policy. Always use this for CRUD actions (inherited from `BaseController`). For custom actions, call it explicitly:

```php
$this->authorizeAction('publish', $record); // → PostCategoryPolicy::publish()
```

---

### Ownership Checks

Two patterns exist for routes that belong to the authenticated user:

**Pattern A — Policy override** (when the resource belongs to a user but admins can also access):

```php
// In Policy
public function view(User $user, Model $model): bool
{
    return $user->id === $model->user_id || $user->can('module.view');
}
```

**Pattern B — No permission middleware** (for pure self-service routes like "my leave requests"):

```php
// In routes/web.php — no permission middleware
Route::prefix('my-leave-requests')->name('my-leave-requests.')->group(function () {
    Route::get('', [LeaveRequestController::class, 'myRequests'])->name('index');
    Route::post('/', [LeaveRequestController::class, 'store'])->name('store');
});

// In controller — direct ownership check
public function showMy(LeaveRequest $record): View
{
    abort_unless($record->user_id === auth()->id(), 403);
    return view('my-leave-requests.show', compact('record'));
}
```

Use Pattern A when admins need access too. Use Pattern B for genuinely self-service endpoints.

---

## Service Layer

### When to Use a Service

`BaseController` handles simple CRUD with `Model::create()` and `$record->update()` directly. Use a service when you need **side-effects** that do not belong in the controller:

- Syncing a relationship after create/update (e.g. assigning roles)
- Sending notifications after certain actions
- Calling an external API
- Hashing or transforming a field before saving (e.g. stripping empty password)
- Creating related records automatically

If none of these apply, skip the service entirely.

### Available Hooks

```php
class PostCategoryService extends BaseService
{
    protected string $modelClass = PostCategory::class;

    protected function beforeCreate(array $data): void
    {
        // Runs before Model::create($data)
        // $data is passed by value — you CANNOT modify what gets saved from here
        // Use this for validation side-effects, not data transformation
    }

    protected function afterCreate(Model $record, array $data): void
    {
        // Runs after the record is saved
        // $record is the new model — use it to sync relationships
        if (! empty($data['tag_ids'])) {
            $record->tags()->sync($data['tag_ids']);
        }
    }

    protected function beforeUpdate(Model $record, array $data): void
    {
        // Same limitation — cannot modify $data for the update
    }

    protected function afterUpdate(Model $record, array $data): void
    {
        if (array_key_exists('tag_ids', $data)) {
            $record->tags()->sync($data['tag_ids'] ?? []);
        }
    }

    protected function beforeDelete(Model $record): void {}
    protected function afterDelete(Model $record): void {}
}
```

Wire the service in the controller constructor:

```php
public function __construct(PostCategoryDataTableController $dataTableController, PostCategoryService $service)
{
    $this->model               = PostCategory::class;
    $this->routePrefix         = 'post-categories';
    $this->viewPrefix          = 'post-categories';
    $this->resourceName        = 'Post Category';
    $this->dataTableController = $dataTableController;
    $this->service             = $service; // BaseController uses this automatically
}
```

### Important: Hook Limitations

`$data` is passed **by value** to `beforeCreate` and `beforeUpdate`. You **cannot** mutate `$data` inside these hooks and have it affect what gets saved. If you need to transform data before saving (e.g. strip an empty password field), **override `update()` directly**:

```php
public function update(Model $record, array $data): Model
{
    if (empty($data['password'])) {
        unset($data['password']); // remove empty password before it overwrites the hash
    }
    return parent::update($record, $data);
}
```

---

## Writing Tests

### Test File Location and Naming

```
tests/Feature/PostCategoryTest.php
```

Create with:
```bash
php artisan make:test --pest PostCategoryTest
```

### beforeEach Setup

Always forget the Spatie permission cache and create the permissions your tests need:

```php
beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    Permission::firstOrCreate(['name' => 'post-categories.view',   'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'post-categories.create', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'post-categories.update', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'post-categories.delete', 'guard_name' => 'web']);
});
```

Use `firstOrCreate` — not `create` — because the migration also creates these permissions, and `RefreshDatabase` runs migrations (not seeders) before the test suite.

### What to Test

For every module, test at minimum:

| Test | What it verifies |
|---|---|
| `can display index page` | 200 with permission |
| `requires permission to view` | 403 without permission |
| `can create a new record` | store → redirect → DB row |
| `validates required fields` | 422 with missing data |
| `cannot create duplicate (unique field)` | `assertSessionHasErrors` |
| `can update an existing record` | update → redirect → DB change |
| `can delete a record` | destroy → redirect → `assertSoftDeleted` |
| `cannot delete when business rule blocks` | `assertStatus(422)` + record still exists |

For modules with ownership rules, also test:
- Owner can perform action
- Non-owner cannot

### Test Examples

```php
it('can display post categories index page', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('post-categories.view');

    actingAs($user)
        ->get(route('post-categories.index'))
        ->assertStatus(200);
});

it('requires permission to view post categories', function () {
    actingAs(User::factory()->create())
        ->get(route('post-categories.index'))
        ->assertStatus(403);
});

it('can create a new post category', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(['post-categories.view', 'post-categories.create']);

    actingAs($user)
        ->post(route('post-categories.store'), [
            'name' => 'Technology',
            'slug' => 'technology',
        ])
        ->assertRedirect(route('post-categories.index'));

    assertDatabaseHas('post_categories', ['name' => 'Technology', 'slug' => 'technology']);
});

it('cannot create a duplicate post category', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(['post-categories.view', 'post-categories.create']);

    PostCategory::factory()->create(['name' => 'Technology', 'slug' => 'technology']);

    actingAs($user)
        ->post(route('post-categories.store'), ['name' => 'Technology', 'slug' => 'technology'])
        ->assertSessionHasErrors('name');
});

it('can update a post category', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(['post-categories.view', 'post-categories.update']);

    $category = PostCategory::factory()->create(['name' => 'Old Name']);

    actingAs($user)
        ->put(route('post-categories.update', $category), ['name' => 'New Name', 'slug' => 'new-name'])
        ->assertRedirect(route('post-categories.index'));

    expect($category->fresh()->name)->toBe('New Name');
});

it('can delete a post category', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(['post-categories.view', 'post-categories.delete']);

    $category = PostCategory::factory()->create();

    actingAs($user)
        ->delete(route('post-categories.destroy', $category))
        ->assertRedirect(route('post-categories.index'));

    assertSoftDeleted('post_categories', ['id' => $category->id]);
});

it('cannot delete a post category that has posts', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(['post-categories.view', 'post-categories.delete']);

    $category = PostCategory::factory()->create();
    // assume Post::factory()->create(['category_id' => $category->id]) exists
    Post::factory()->create(['category_id' => $category->id]);

    actingAs($user)
        ->delete(route('post-categories.destroy', $category))
        ->assertStatus(422);

    expect(PostCategory::find($category->id))->not->toBeNull();
});
```

---

## Removing a Module

```bash
php artisan remove:crud-module PostCategory
```

**What it removes:**
- Migration (and drops the table if you confirm)
- Model, Observer, Policy, Request, Controller, DataTableController
- Views directory

**Data protection:** If the table has rows, the command will display a warning and require you to type the table name to confirm before dropping it. Use `--force` to skip the confirmation.

**What it does NOT remove:**
- The route line in `routes/web.php` (remove manually)
- The sidebar link (remove manually)
- Permissions assigned to roles (cleaned up by the migration rollback, or remove manually)

After removal, clean up `routes/web.php` and the sidebar.

---

## Reference

### BaseController Hooks

Override these in your controller — never override `store()` or `update()` with a typed parameter.

| Method | Purpose |
|---|---|
| `requestClass(): ?string` | Return the form request class to use for store/update |
| `messages(): array` | Override flash messages per action |
| `fileFields(): array` | Map field name → storage path for file uploads |
| `createViewData(): array` | Extra data passed to the create form view |
| `editViewData(Model): array` | Extra data passed to the edit form view |
| `beforeDestroy(Model): void` | Guard logic before delete; `abort()` here to cancel |
| `afterDestroy(Model): void` | Cleanup after delete (file removal is automatic) |

**Why never override `store(Request $request)`?**
PHP enforces method signature compatibility. Overriding `store(Request $request)` with `store(StorePostCategoryRequest $request)` causes a fatal `Declaration must be compatible` error. Use `requestClass()` instead — `BaseController` resolves and validates the request internally.

---

### BaseFormRequest

| Method | Returns | Use |
|---|---|---|
| `recordId()` | `int\|string\|null` | The `{record}` route parameter; `null` on create |
| `isUpdating()` | `bool` | `true` when `recordId()` is not null |
| `authorize()` | `bool` | Always `true` — authorization is handled by the policy |

---

### Observer

The generated observer tracks `created_by`, `updated_by`, and `deleted_by` automatically. Do not replicate this logic in the controller or service. Extend the observer for additional side-effects (e.g. auto-generating a slug):

```php
public function creating(PostCategory $postCategory): void
{
    $postCategory->created_by = auth()->id();
    $postCategory->updated_by = auth()->id();
    $postCategory->slug ??= Str::slug($postCategory->name); // computed field
}
```

---

### DataTable Controller

| Method to override | Purpose |
|---|---|
| `indexQuery(): Builder` | Base query — add scopes, eager loads, ordering |
| `dataTableColumns(): array` | Computed columns not in the DB (`['col_name' => fn($record) => string]`) |
| `actionColumn($record): string` | Action buttons HTML |
| `tableColumns(): array` | Column definitions for the front-end DataTable config |
| `applyFilters(EloquentDataTable, Request): EloquentDataTable` | Additional filters (date range, status, etc.) |

**Computed columns** (e.g. `updated_by_name`) must have `'searchable' => false` and `'orderable' => false` in `tableColumns()` because Yajra cannot sort/search on them with a DB query. Computed columns built from relationships (like `withCount`) must also set `'searchable' => false`.

---

### Form Input Components

Use the shared partials in `resources/views/layouts/form/inputs/`:

```blade
{{-- Text input --}}
@include('layouts.form.inputs.text', ['var' => [
    'name'        => 'name',
    'label'       => 'Name',
    'value'       => $record?->name,
    'placeholder' => 'Enter name',
    'div'         => 'col-md-6',
    'required'    => true,
    'autofocus'   => true,
]])

{{-- Textarea --}}
@include('layouts.form.inputs.textarea', ['var' => [
    'name'  => 'description',
    'label' => 'Description',
    'value' => $record?->description,
    'rows'  => 4,
    'div'   => 'col-md-12',
]])

{{-- Select --}}
@include('layouts.form.inputs.select', ['var' => [
    'name'    => 'status',
    'label'   => 'Status',
    'value'   => $record?->status,
    'options' => ['active' => 'Active', 'inactive' => 'Inactive'],
    'div'     => 'col-md-6',
]])
```

---

### Reusable File Input

```blade
@include('layouts.form.inputs.file', ['var' => [
    'name'    => 'supporting_document',
    'label'   => 'Supporting Document',
    'value'   => $record?->supporting_document,
    'accept'  => '.pdf,.doc,.docx',
    'div'     => 'col-md-6',
]])
```

Register the field in the controller:

```php
protected function fileFields(): array
{
    return [
        'supporting_document' => 'post-categories/documents',
    ];
}
```

`BaseController` handles upload on create, replacement on update, and deletion when the record is deleted.

---

### Select2 Dropdowns

For a select with many options or AJAX search, add `select2` to the input's class in your Blade and ensure the `select2` JS is loaded in the layout.

---

### JavaScript Timing Pattern

The `init{ModuleName}DataTable()` function retries with `setTimeout` if jQuery or Bootstrap is not yet loaded. Do not remove this pattern from the generated views. If you add other JavaScript that depends on jQuery, wrap it in the same guard:

```javascript
function initMyWidget() {
    if (typeof $ !== 'undefined' && typeof bootstrap !== 'undefined') {
        $(document).ready(function () {
            // your code here
        });
    } else {
        setTimeout(initMyWidget, 100);
    }
}
initMyWidget();
```

---

## Seeded Permissions Reference

These permissions are created by migrations and assigned in seeders. Use them directly in route middleware and policy checks.

| Module | Permissions |
|---|---|
| `users` | `users.view` `.create` `.update` `.delete` |
| `roles` | `roles.view` `.create` `.update` `.delete` |
| `permissions` | `permissions.view` `.update` |
| `departments` | `departments.view` `.create` `.update` `.delete` |
| `designations` | `designations.view` `.create` `.update` `.delete` |
| `employees` | `employees.view` `.create` `.update` `.delete` |
| `products` | `products.view` `.create` `.update` `.delete` |
| `leave-types` | `leave-types.view` `.create` `.update` `.delete` |
| `leave-requests` | `leave-requests.view` `.create` `.update` `.delete` `.approve` `.reject` `.cancel` |
| `leave-balances` | `leave-balances.view` |
| `employee-categories` | `employee-categories.view` `.create` `.update` `.delete` |
| `holidays` | `holidays.view` `.create` `.update` `.delete` |
| `landing-page-sections` | `landing-page-sections.view` `.update` |
| `activity-log` | `activity-log.view` |
| `schedules` | `schedules.view` `.create` `.update` `.delete` `.assign` |
| `shifts` | `shifts.view` `.create` `.update` `.delete` |

---

## Common Mistakes

### 1. Using space-format permission strings

**Wrong:**
```blade
@can('create post categories')
```
```php
->middleware('permission:create post-categories')
```

**Correct:**
```blade
@can('post-categories.create')
```
```php
->middleware('permission:post-categories.create')
```

The entire codebase uses dot-notation exclusively. Any space-format string will silently fail (return 403) because no such permission exists in the database.

---

### 2. Overriding store()/update() with a typed parameter

**Wrong — fatal PHP error:**
```php
public function store(PostCategoryRequest $request): RedirectResponse
{
    // Declaration must be compatible with BaseController::store(Request $request)
}
```

**Correct — use the hook:**
```php
protected function requestClass(): ?string
{
    return PostCategoryRequest::class;
}
```

---

### 3. Forgetting to forget the permission cache in tests

```php
beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions(); // required
    // ...
});
```

Without this, permission checks in tests return stale results from a previous test.

---

### 4. Using Role::create() for pre-seeded roles in tests

Roles `Superuser`, `Admin`, and `Employee` are created by a migration (not a seeder), so they exist in the test database after `RefreshDatabase` runs migrations. Using `Role::create()` for these names will throw `RoleAlreadyExists`.

**Wrong:**
```php
$role = Role::create(['name' => 'Admin', 'guard_name' => 'web']); // throws
```

**Correct:**
```php
$role = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
```

---

### 5. Using assertSee() on a DataTable page

The DataTable loads its rows via AJAX. `assertSee('some record name')` on the index page will always fail because the HTML response only contains the table shell.

**Wrong:**
```php
actingAs($user)->get(route('post-categories.index'))->assertSee('Technology');
```

**Correct — test the datatable endpoint:**
```php
actingAs($user)
    ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
    ->getJson(route('post-categories.datatable', ['draw' => 1, 'start' => 0, 'length' => 100,
        'search' => ['value' => 'Technology', 'regex' => 'false'],
        'columns' => [['data' => 'name', 'name' => 'name', 'searchable' => 'true',
                        'orderable' => 'true', 'search' => ['value' => '', 'regex' => 'false']]],
        'order' => [['column' => 0, 'dir' => 'asc']],
    ]))
    ->assertStatus(200)
    ->assertJsonFragment(['name' => 'Technology']);
```

Or test the presence in the database with `assertDatabaseHas`.

---

### 6. Using abort(403/422) where a redirect with error is expected

`abort(403)` and `abort(422)` return HTTP error responses, not redirects. In tests, use:

```php
->assertForbidden()    // 403
->assertStatus(422)    // 422
->assertRedirect(...)  // only for actual redirects
```

---

### 7. Using `role:` middleware

```php
// WRONG — blocks Superusers because Gate::before does not fire for hasRole()
->middleware('role:Admin')
```

```php
// CORRECT — Superusers pass because can() goes through Gate::before
->middleware('permission:post-categories.view')
```

`$user->hasRole()` bypasses `Gate::before`, so the Superuser short-circuit does not apply. Always protect routes with `permission:` middleware, never `role:`.

---

### 8. Placing extra routes after {record} in the crudModule closure

The `crudModule` macro registers extra routes **before** the `/{record}` wildcard. However, if you manually register routes outside the closure, ensure static paths come before wildcard routes in the file:

```php
// WRONG — /post-categories/export is matched by /{record} if registered after it
Route::get('/post-categories/{record}', ...);
Route::get('/post-categories/export', ...); // 404 or wrong match
```

Using the closure argument to `crudModule` handles this automatically.

---

## Customising Stubs

Stubs live in `stubs/crud-module/`. Edit them to change what `make:crud-module` generates for all future modules. Tokens:

| Token | Resolves to |
|---|---|
| `{ModuleName}` | `PostCategory` |
| `{moduleName}` | `postCategory` |
| `{Module Name}` | `Post Category` |
| `{module name}` | `post category` |
| `{module-names}` | `post-categories` |
| `{module_names}` | `post_categories` |
| `{Module Names}` | `Post Categories` |
