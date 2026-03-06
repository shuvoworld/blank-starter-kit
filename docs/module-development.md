# Module Development Guide

A step-by-step reference for building CRUD modules that follow this project's architecture.

---

## Table of Contents

- [Quick Start Checklist](#quick-start-checklist)
- [Architecture Overview](#architecture-overview)
- [File Structure](#file-structure)
- [Step-by-Step Walkthrough](#step-by-step-walkthrough)
  - [1. Scaffold the Module](#1-scaffold-the-module)
  - [2. Define the Migration](#2-define-the-migration)
  - [3. Update the Model](#3-update-the-model)
  - [4. Add Validation Rules](#4-add-validation-rules)
  - [5. Register the Route](#5-register-the-route)
  - [6. Permissions (Automatic)](#6-permissions-automatic)
  - [7. Assign Permissions to Roles](#7-assign-permissions-to-roles)
  - [8. Add a Sidebar Link](#8-add-a-sidebar-link)
  - [9. Customise the Views](#9-customise-the-views)
- [Permission & Authorization System](#permission--authorization-system)
  - [How the System Works](#how-the-system-works)
  - [Layer 1 — Database Permissions](#layer-1--database-permissions)
  - [Layer 2 — Policy](#layer-2--policy)
  - [Layer 3 — Controller](#layer-3--controller)
  - [Layer 4 — DataTable Query Scoping](#layer-4--datatable-query-scoping)
  - [Layer 5 — Blade Views](#layer-5--blade-views)
  - [Checking Permissions in PHP (Any Context)](#checking-permissions-in-php-any-context)
  - [Common Mistakes](#common-mistakes)
- [Removing a Module](#removing-a-module)
- [Reference](#reference)
  - [Observer](#observer)
  - [DataTable Controller](#datatable-controller)
  - [Controller](#controller)
  - [Form Input Components](#form-input-components)
  - [Reusable Media File Input](#reusable-media-file-input)
  - [Select2 Dropdowns](#select2-dropdowns)
  - [JavaScript Timing Pattern](#javascript-timing-pattern)
- [Customising Stubs](#customising-stubs)

---

## Quick Start Checklist

```
[ ] 1. php artisan make:crud-module YourModuleName
[ ] 2. Add columns to the generated migration → php artisan migrate
       (4 permissions are created automatically by the migration)
[ ] 3. Add columns to $fillable (and casts if needed) in the Model
[ ] 4. Add validation rules to StoreYourModuleRequest and UpdateYourModuleRequest
[ ] 5. Add Route::crudModule(...) to routes/web.php
[ ] 6. Assign permissions to roles (via Roles UI or role seeder)
[ ] 7. Add a sidebar link
[ ] 8. Customise views (add form fields, table columns, show page)
```

---

## Architecture Overview

Every module is built on three base classes:

| Class | Purpose |
|---|---|
| `BaseController` | Handles `index`, `create`, `edit`, `show`, `destroy` with built-in authorization and flash messages |
| `BaseDataTableController` | Handles the server-side DataTables AJAX endpoint |
| `BasePolicy` | Provides Superuser bypass and default CRUD permission checks; extended by every module policy |

```
YourModuleController          → extends BaseController
YourModuleDataTableController → extends BaseDataTableController
YourModulePolicy              → extends BasePolicy
```

Authorization flows through three layers in order:

```
Route Middleware → BaseController → Policy → Spatie Permission
```

---

## File Structure

The scaffold command generates these 11 files:

```
database/migrations/
    YYYY_MM_DD_HHMMSS_create_{module_names}_table.php
        └─ creates the table AND seeds 4 permissions automatically

app/
    Models/
        YourModule.php
    Observers/
        YourModuleObserver.php
    Policies/
        YourModulePolicy.php           ← extends BasePolicy (8 lines)
    Http/
        Requests/
            StoreYourModuleRequest.php
            UpdateYourModuleRequest.php
        Controllers/
            YourModuleController.php
            YourModuleDataTableController.php

resources/views/
    your-modules/
        index.blade.php
        form.blade.php
        show.blade.php
```

---

## Step-by-Step Walkthrough

This section walks through every required step using `PostCategory` as the example module.

---

### 1. Scaffold the Module

```bash
php artisan make:crud-module PostCategory
```

Accepts PascalCase names including multi-word (e.g. `LeaveType`, `PostCategory`). Use `--force` to overwrite existing files.

The command prints the remaining steps in your terminal after generation.

---

### 2. Define the Migration

**File:** `database/migrations/YYYY_MM_DD_HHMMSS_create_post_categories_table.php`

The generated migration creates the table **and** seeds 4 permissions automatically. Add your module-specific columns in the gap shown:

```php
Schema::create('post_categories', function (Blueprint $table) {
    $table->id();

    // ↓ Add your columns here
    $table->string('name');
    $table->string('slug')->unique();
    $table->text('description')->nullable();
    $table->boolean('is_active')->default(true);
    // ↑ Add your columns here

    $table->timestamps();
    $table->unsignedBigInteger('created_by')->nullable();
    $table->unsignedBigInteger('updated_by')->nullable();
    $table->softDeletes();
    $table->unsignedBigInteger('deleted_by')->nullable();
});

// Permissions are created here — do not remove this block
collect(['post-categories.view', 'post-categories.create', 'post-categories.update', 'post-categories.delete'])
    ->each(fn ($name) => Permission::firstOrCreate(
        ['name' => $name, 'guard_name' => 'web'],
        ['module' => 'post-categories']
    ));
```

Then run:

```bash
php artisan migrate
```

The `down()` method reverses both: drops the table and deletes all permissions for this module.

---

### 3. Update the Model

**File:** `app/Models/PostCategory.php`

Add your columns to `$fillable` and declare any casts:

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

public function casts(): array
{
    return [
        'is_active' => 'boolean',
    ];
}
```

> The scaffold already includes `HasActivityLog`, `HasFactory`, `SoftDeletes`, and the `booted()` observer hook. Do not remove these.

Add relationships, scopes, or accessors below the casts as needed:

```php
public function scopeActive(Builder $query): Builder
{
    return $query->where('is_active', true);
}
```

---

### 4. Add Validation Rules

Both request files are generated with a placeholder `name` field. Replace with rules matching your actual columns.

**`app/Http/Requests/StorePostCategoryRequest.php`**

```php
public function rules(): array
{
    return [
        'name'        => ['required', 'string', 'max:255', 'unique:post_categories,name'],
        'slug'        => ['required', 'string', 'max:255', 'unique:post_categories,slug'],
        'description' => ['nullable', 'string'],
        'is_active'   => ['nullable', 'boolean'],
    ];
}
```

**`app/Http/Requests/UpdatePostCategoryRequest.php`**

The update request excludes the current record from unique checks using the `record` route parameter:

```php
public function rules(): array
{
    $id = $this->route('record');

    return [
        'name'        => ['required', 'string', 'max:255', 'unique:post_categories,name,' . $id],
        'slug'        => ['required', 'string', 'max:255', 'unique:post_categories,slug,' . $id],
        'description' => ['nullable', 'string'],
        'is_active'   => ['nullable', 'boolean'],
    ];
}
```

Add custom messages in the `messages()` method of both requests if needed.

Both `authorize()` methods return `true` — authorization is handled by the Policy, not the Form Request.

---

### 5. Register the Route

**File:** `routes/web.php`

Add the two `use` imports at the top of the file, then add one line inside the `Route::middleware('auth')` group:

```php
use App\Http\Controllers\PostCategoryController;
use App\Http\Controllers\PostCategoryDataTableController;

Route::middleware('auth')->group(function () {
    // ... existing routes ...

    Route::crudModule('post-categories', PostCategoryController::class, PostCategoryDataTableController::class);
});
```

This single call registers all 8 routes with permission middleware automatically:

| Method | URI | Route name | Permission required |
|--------|-----|------------|---------------------|
| GET | `/post-categories/datatable` | `post-categories.datatable` | `post-categories.view` |
| GET | `/post-categories` | `post-categories.index` | `post-categories.view` |
| GET | `/post-categories/create` | `post-categories.create` | `post-categories.create` |
| POST | `/post-categories` | `post-categories.store` | `post-categories.create` |
| GET | `/post-categories/{record}` | `post-categories.show` | `post-categories.view` |
| GET | `/post-categories/{record}/edit` | `post-categories.edit` | `post-categories.update` |
| PUT | `/post-categories/{record}` | `post-categories.update` | `post-categories.update` |
| DELETE | `/post-categories/{record}` | `post-categories.destroy` | `post-categories.delete` |

**Need extra routes?** Pass a closure as the fourth argument:

```php
Route::crudModule('post-categories', PostCategoryController::class, PostCategoryDataTableController::class, function () {
    Route::post('/{record}/publish', [PostCategoryController::class, 'publish'])
        ->name('publish')
        ->middleware('permission:post-categories.update');
});
```

---

### 6. Permissions (Automatic)

No manual work required here — `php artisan migrate` already created these four permissions in the database:

```
post-categories.view
post-categories.create
post-categories.update
post-categories.delete
```

The permission key is always the route prefix in kebab-case: `post-categories`.

If you roll back the migration, these permissions are automatically deleted.

---

### 7. Assign Permissions to Roles

The permissions exist in the database, but no role has them yet (except Superuser, who bypasses all checks automatically).

Assign permissions using the **Roles UI** in the application, or add them to your role seeder:

```php
// In RbacSeeder or a dedicated role seeder:
$admin = Role::findByName('Admin');
$admin->givePermissionTo([
    'post-categories.view',
    'post-categories.create',
    'post-categories.update',
    'post-categories.delete',
]);
```

---

### 8. Add a Sidebar Link

**File:** `resources/views/layouts/dashboard/partials/sidebar.blade.php`

The sidebar is divided into three role-priority blocks. Find the right block and add your link inside it:

```blade
@php
    $userRole = auth()->user()->roles->pluck('name')->first();
    $rolePriority = ['Superuser' => 3, 'Admin' => 2, 'Employee' => 1];
    $priority = $rolePriority[$userRole] ?? 0;
@endphp

@if($priority >= 3)
    {{-- Superuser Menu — links visible only to Superusers --}}

@elseif($priority >= 2)
    {{-- Admin Menu — links visible to Admins (and Superusers via the block above) --}}

@else
    {{-- Employee Menu — links visible to all authenticated users --}}

@endif
```

Add your `<li>` inside the appropriate block:

```blade
<li class="nav-item">
    <a href="{{ route('post-categories.index') }}"
       class="nav-link {{ request()->routeIs('post-categories.*') ? 'active' : '' }}">
        <i class="nav-icon bi bi-grid"></i>
        <p>Post Categories</p>
    </a>
</li>
```

Use Bootstrap Icons (`bi bi-*`) for `nav-icon`. The `request()->routeIs('post-categories.*')` wildcard highlights the link for all sub-routes of the module.

---

### 9. Customise the Views

At this point the module is fully functional with a basic name-only form. Customise the three generated views to match your columns.

#### form.blade.php

Add `@include` calls for each field using the form input partials:

```blade
<div class="row">
    @include('layouts.form.inputs.text', ['var' => [
        'name'        => 'name',
        'label'       => 'Name',
        'value'       => $record?->name,
        'placeholder' => 'e.g., Technology',
        'div'         => 'col-md-6',
        'required'    => true,
        'autofocus'   => true,
    ]])

    @include('layouts.form.inputs.select', ['var' => [
        'name'     => 'is_active',
        'label'    => 'Status',
        'value'    => $record?->is_active ?? 1,
        'options'  => [1 => 'Active', 0 => 'Inactive'],
        'div'      => 'col-md-4',
        'required' => true,
        'select2'  => true,
    ]])
</div>
```

#### index.blade.php

Update `tableColumns()` in `PostCategoryDataTableController` to match the columns you want in the table.

#### show.blade.php

Add rows to the detail table for each field you want to display.

---

## Permission & Authorization System

This section is a practical implementation guide. It covers every layer where permissions are enforced and shows exactly what to write in each case.

---

### How the System Works

There are **five layers** where access is controlled. Each has a distinct job:

```
Layer 1 — Migration         Creates the permissions in the database
Layer 2 — Policy            Defines who can do what (class-level and row-level)
Layer 3 — Controller        Enforces the policy before executing an action
Layer 4 — DataTable query   Scopes what rows a user can see in the list
Layer 5 — Blade views       Shows or hides UI elements (buttons, links, sections)
```

A request to edit a record flows through all of them:

```
GET /post-categories/5/edit

  → Route middleware: does user have 'post-categories.update' permission?
      NO  → 403 (controller never runs)
      YES ↓

  → BaseController::edit() calls authorizeAction('update', $record)
      → PostCategoryPolicy::before()  — Superuser? return true immediately
      → PostCategoryPolicy::update()  — return $user->can('post-categories.update')
          NO  → 403
          YES → render the edit view

  → Blade view renders — @can('update', $postCategory) controls buttons
```

> **Superuser bypass:** `PostCategoryPolicy::before()` (inherited from `BasePolicy`) returns `true` for any user with the `Superuser` role. Every layer respects this — no further checks run.

---

### Layer 1 — Database Permissions

#### Naming convention

All permissions use **dot-notation**: `{module-key}.{action}`

The module key is the **kebab-case plural** of the model name — the same as the route prefix.

| Model | Module key | Standard permissions |
|-------|-----------|----------------------|
| `PostCategory` | `post-categories` | `post-categories.view` `.create` `.update` `.delete` |
| `LeaveRequest` | `leave-requests` | `leave-requests.view` `.create` `.update` `.delete` |
| `Department` | `departments` | `departments.view` `.create` `.update` `.delete` |

For custom actions beyond CRUD, add extra permissions with the same pattern:

```
leave-requests.approve
leave-requests.reject
leave-requests.cancel
```

#### Creating permissions — in the migration (standard)

The generated migration creates the 4 standard permissions automatically. Do not remove this block:

```php
collect(['post-categories.view', 'post-categories.create', 'post-categories.update', 'post-categories.delete'])
    ->each(fn ($name) => Permission::firstOrCreate(
        ['name' => $name, 'guard_name' => 'web'],
        ['module' => 'post-categories']
    ));
```

#### Creating permissions — for custom actions

Add extra `firstOrCreate` calls in the same migration block:

```php
collect([
    'post-categories.view',
    'post-categories.create',
    'post-categories.update',
    'post-categories.delete',
    'post-categories.approve',   // ← custom action
])
    ->each(fn ($name) => Permission::firstOrCreate(
        ['name' => $name, 'guard_name' => 'web'],
        ['module' => 'post-categories']
    ));
```

Remember to also delete them in `down()`:

```php
Permission::where('module', 'post-categories')->delete();
```

---

### Layer 2 — Policy

The policy is where access rules live. Laravel auto-discovers it: `PostCategory` model → `PostCategoryPolicy`.

#### Case A: Standard CRUD (no custom rules)

The generated policy is complete as-is. No changes needed:

```php
class PostCategoryPolicy extends BasePolicy
{
    protected function resource(): string
    {
        return 'post-categories';
    }
}
```

`BasePolicy` provides default implementations for `viewAny`, `view`, `create`, `update`, and `delete` — all delegating to `$user->can("{resource}.{action}")`. The `before()` method handles the Superuser bypass.

#### Case B: Users can only access their own records

Override `viewAny` and `view`. Users without the permission still access their own records; users with the permission see everything:

```php
class LeaveRequestPolicy extends BasePolicy
{
    protected function resource(): string
    {
        return 'leave-requests';
    }

    // viewAny returns true for all authenticated users
    // (list is scoped in the DataTable query — see Layer 4)
    public function viewAny(User $user): bool
    {
        return true;
    }

    // Row-level: own record always allowed; others need the permission
    public function view(User $user, Model $model): bool
    {
        return $user->id === $model->user_id || $user->can('leave-requests.view');
    }

    public function update(User $user, Model $model): bool
    {
        return $user->id === $model->user_id || $user->can('leave-requests.update');
    }
}
```

> **Important:** Always type-hint `Model $model` (not the specific model type e.g. `LeaveRequest $model`) in policy overrides. PHP does not allow narrowing parameter types in method overrides — using the specific type causes a fatal error.

#### Case C: Status-based rules (e.g. only pending records can be edited)

```php
public function update(User $user, Model $model): bool
{
    // Own pending record: user can edit
    if ($model->user_id === $user->id && $model->isPending()) {
        return true;
    }

    // Admin with permission: can edit any
    return $user->can('leave-requests.update');
}
```

#### Case D: Custom actions (approve, reject, cancel)

Add a method named after the custom ability. The method name must exactly match what you pass to `authorizeAction()`:

```php
public function approve(User $user, Model $model): bool
{
    return $model->isPending() && $user->can('leave-requests.approve');
}

public function reject(User $user, Model $model): bool
{
    return $model->isPending() && $user->can('leave-requests.reject');
}
```

---

### Layer 3 — Controller

#### What BaseController handles automatically

`BaseController` calls `authorizeAction()` for all four inherited methods. You do not repeat this yourself:

| Inherited method | Ability enforced automatically |
|-----------------|-------------------------------|
| `index()` | `viewAny` |
| `create()` | `create` |
| `edit()` | `update` |
| `destroy()` | `delete` |

#### What you must add yourself

`store()` and `update()` are not in `BaseController` (they need module-specific Form Requests), so you call `authorizeAction()` manually:

```php
public function store(StorePostCategoryRequest $request): RedirectResponse
{
    $this->authorizeAction('create');                     // no record — class-level check

    PostCategory::create($request->validated());

    return $this->successRedirect('created');
}

public function update(UpdatePostCategoryRequest $request, int|string $record): RedirectResponse
{
    $postCategory = $this->findRecord($record);
    $this->authorizeAction('update', $postCategory);      // pass the record — row-level check

    $postCategory->update($request->validated());

    return $this->successRedirect('updated');
}
```

If you override `show()` to render a view (the base default just redirects to edit):

```php
public function show(int|string $record): View
{
    $postCategory = $this->findRecord($record);
    $this->authorizeAction('view', $postCategory);

    return view('post-categories.show', compact('postCategory'));
}
```

#### Custom actions

For actions beyond CRUD, add the method and call `authorizeAction()` with the ability name matching your Policy method:

```php
public function approve(Request $request, int|string $record): RedirectResponse
{
    $leaveRequest = $this->findRecord($record);
    $this->authorizeAction('approve', $leaveRequest);    // → LeaveRequestPolicy::approve()

    $leaveRequest->update(['status' => 'approved']);

    return $this->successRedirect('approved');
}
```

#### Quick reference: when to call `authorizeAction()` in a module controller

| Method | Call authorizeAction? | Notes |
|--------|-----------------------|-------|
| `store()` | **Yes** | Always |
| `update()` | **Yes** | Always, pass the record |
| `show()` | **Only if overriding** | Base default redirects to edit (already authorized) |
| `approve()` / custom | **Yes** | Always, pass the record |
| `index()` | No | Base handles it |
| `create()` | No | Base handles it |
| `edit()` | No | Base handles it |
| `destroy()` | No | Base handles it |

> **Never call `$this->authorize()` directly.** Always use `$this->authorizeAction()`. It is the single authorization entry point for module controllers.

---

### Layer 4 — DataTable Query Scoping

When `viewAny` returns `true` for all users (Case B in the Policy), the list page loads for everyone — but the rows returned must be scoped. This is handled in the DataTable controller's `indexQuery()` method.

**Example: regular users see only their own records; users with the view permission see all**

```php
// In LeaveRequestDataTableController:
protected function indexQuery(): Builder
{
    $user = auth()->user();

    return LeaveRequest::query()
        ->with(['user', 'leaveType'])
        ->when(
            ! $user->can('leave-requests.view'),
            fn ($q) => $q->where('user_id', $user->id)
        );
}
```

If the module has no own-record scoping (all users with access see all rows), leave `indexQuery()` without the `when()` condition:

```php
protected function indexQuery(): Builder
{
    return PostCategory::query()->with(['createdBy']);
}
```

---

### Layer 5 — Blade Views

Use `@can` / `@cannot` to show or hide buttons, links, and sections. The Superuser bypass fires here too.

#### Gating action buttons (show.blade.php, index.blade.php rows)

Pass the record as the second argument to trigger a row-level policy check:

```blade
@can('update', $postCategory)
    <a href="{{ route('post-categories.edit', $postCategory) }}" class="btn btn-primary">Edit</a>
@endcan

@can('delete', $postCategory)
    <button class="btn btn-danger btn-delete"
            data-url="{{ route('post-categories.destroy', $postCategory) }}">Delete</button>
@endcan
```

#### Gating the Create button (class-level, no record)

Pass the model class string instead of a record instance:

```blade
@can('create', App\Models\PostCategory::class)
    <a href="{{ route('post-categories.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> New Post Category
    </a>
@endcan
```

#### Gating custom action buttons

```blade
@can('approve', $leaveRequest)
    <button class="btn btn-success btn-approve"
            data-url="{{ route('leave-requests.approve', $leaveRequest) }}">Approve</button>
@endcan
```

#### Checking a permission string directly (no Policy, no record)

Use this for sidebar links and other UI that is not tied to a specific record:

```blade
@can('post-categories.view')
    <li>
        <a href="{{ route('post-categories.index') }}">Post Categories</a>
    </li>
@endcan
```

#### Sidebar link gating

In `resources/views/layouts/dashboard/partials/sidebar.blade.php`, wrap each module link with the view permission:

```blade
@can('departments.view')
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('departments.*') ? 'active' : '' }}"
           href="{{ route('departments.index') }}">
            <i class="bi bi-building"></i>
            <span>Departments</span>
        </a>
    </li>
@endcan
```

---

### Checking Permissions in PHP (Any Context)

Always use `$user->can()` — never `$user->hasPermissionTo()` or `$user->hasRole()` directly. `can()` goes through the Laravel Gate, which fires the Superuser bypass. The other methods bypass the Gate entirely.

```php
// In a controller, job, service, or anywhere you have a User instance:
$user = auth()->user();

if ($user->can('post-categories.approve')) {
    // ...
}

// Checking role (goes through Gate):
if ($user->can('some-permission')) { ... }

// ❌ Never do this — bypasses Gate, Superuser bypass does NOT fire:
if ($user->hasPermissionTo('post-categories.approve')) { ... }
if ($user->hasRole('Admin')) { ... }
```

---

### Common Mistakes

**1. Forgetting `authorizeAction()` in `store()` or `update()`**

Route middleware protects the page load (GET). It does not protect the form submit (POST/PUT). Without `authorizeAction()` in `store()` and `update()`, any authenticated user can write data.

**2. Using `$this->authorize()` instead of `$this->authorizeAction()`**

```php
// ❌ Wrong
$this->authorize('create', PostCategory::class);

// ✅ Correct
$this->authorizeAction('create');
```

**3. Overriding `authorizeAction` in a module controller**

The base implementation already resolves `$this->model`. Any override is redundant and should be deleted.

**4. Using the specific model type in a Policy override**

```php
// ❌ Wrong — "Declaration must be compatible" fatal error
public function update(User $user, PostCategory $model): bool { ... }

// ✅ Correct
public function update(User $user, Model $model): bool { ... }
```

**5. Using `hasPermissionTo()` or `hasRole()` instead of `can()`**

```php
// ❌ Wrong — Superuser bypass does not fire
$user->hasPermissionTo('post-categories.view');

// ✅ Correct — Superuser bypass fires correctly
$user->can('post-categories.view');
```

**6. Not scoping the DataTable query when `viewAny` returns `true`**

If `viewAny` allows all users to load the list page, the DataTable query must use `->when()` to filter rows by `user_id`. Without it, every user sees every row.

---

## Removing a Module

Use the `remove:crud-module` command to delete all artifacts created by `make:crud-module`:

```bash
php artisan remove:crud-module PostCategory
```

Before deleting anything the command prints an inventory of what it found:

```
The following will be permanently removed:

  PHP Files:
    [DELETE]  app/Models/PostCategory.php
    [DELETE]  app/Observers/PostCategoryObserver.php
    [DELETE]  app/Policies/PostCategoryPolicy.php
    ...

  Permissions:
    [DELETE]  post-categories.view
    [DELETE]  post-categories.create
    [DELETE]  post-categories.update
    [DELETE]  post-categories.delete
```

Add `--force` to skip the confirmation prompt.

After the command runs, manually remove:
1. **Route line** in `routes/web.php` — `Route::crudModule(...)` call and its `use` imports
2. **Sidebar link** in `resources/views/layouts/dashboard/partials/sidebar.blade.php`
3. **Any references** in seeders, other controllers, or existing tests

---

## Reference

### Observer

**File:** `app/Observers/PostCategoryObserver.php`

Automatically tracks `created_by`, `updated_by`, and `deleted_by`. No changes required for standard modules.

Add cascade logic here if deleting this record should affect related records:

```php
public function deleting(PostCategory $postCategory): void
{
    $postCategory->deleted_by = auth()->id();
    $postCategory->saveQuietly();

    // Cancel related records before this record is gone
    $postCategory->posts()->each(fn ($post) => $post->update(['status' => 'draft']));
}
```

Use `each()` with individual saves (not mass `update()`) so `HasActivityLog` records each change.

---

### DataTable Controller

**File:** `app/Http/Controllers/PostCategoryDataTableController.php`

#### Constructor — required setup

```php
public function __construct()
{
    $this->model = PostCategory::class;
    $this->routePrefix = 'post-categories';
    $this->rawColumns = ['status_badge']; // declare any HTML columns; 'action' is always included
}
```

#### `indexQuery()` — base query

```php
protected function indexQuery(): Builder
{
    return PostCategory::query()
        ->with(['updatedBy'])
        ->when(request('filter_status'), fn ($q, $v) => $q->where('is_active', $v))
        ->orderBy('name');
}
```

#### `dataTableColumns()` — computed/formatted columns

```php
protected function dataTableColumns(): array
{
    return [
        'status_badge' => fn (PostCategory $r) => $r->is_active
            ? '<span class="badge bg-success">Active</span>'
            : '<span class="badge bg-secondary">Inactive</span>',
    ];
}
```

#### `tableColumns()` — thead + DataTables JS config

```php
public function tableColumns(): array
{
    return [
        ['data' => 'DT_RowIndex',  'name' => 'DT_RowIndex', 'label' => '#',       'width' => '50',  'orderable' => false, 'searchable' => false, 'className' => 'text-center'],
        ['data' => 'name',         'name' => 'name',         'label' => 'Name'],
        ['data' => 'status_badge', 'name' => 'is_active',    'label' => 'Status',  'orderable' => false, 'searchable' => false, 'className' => 'text-center'],
        ['data' => 'updated_at',   'name' => 'updated_at',   'label' => 'Updated', 'orderable' => true, 'searchable' => false],
        ['data' => 'action',       'name' => 'action',       'label' => 'Actions', 'width' => '180', 'orderable' => false, 'searchable' => false, 'className' => 'text-center'],
    ];
}
```

#### `actionColumn()` — override to customise buttons

> Do **not** type-hint the parameter. The base declares `actionColumn($record): string` without a type, and narrowing it causes a fatal `Declaration must be compatible` error.

```php
protected function actionColumn($postCategory): string
{
    $showUrl   = route('post-categories.show', $postCategory);
    $editUrl   = route('post-categories.edit', $postCategory);
    $deleteUrl = route('post-categories.destroy', $postCategory);

    return '
        <div class="btn-group btn-group-sm">
            <a href="' . $showUrl . '" class="btn btn-info" title="View"><i class="bi bi-eye"></i></a>
            <a href="' . $editUrl . '" class="btn btn-primary" title="Edit"><i class="bi bi-pencil"></i></a>
            <button type="button" class="btn btn-danger btn-delete"
                data-url="' . $deleteUrl . '" data-name="' . e($postCategory->name) . '" title="Delete">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    ';
}
```

---

### Controller

**File:** `app/Http/Controllers/PostCategoryController.php`

The constructor wires up `BaseController`. No additional authorization setup is needed.

```php
public function __construct(PostCategoryDataTableController $dataTableController)
{
    $this->model = PostCategory::class;       // authorizeAction() reads this automatically
    $this->routePrefix = 'post-categories';
    $this->viewPrefix = 'post-categories';
    $this->resourceName = 'Post Category';
    $this->dataTableController = $dataTableController;
}
```

#### Override hooks

**`createViewData()` / `editViewData()`** — pass extra data to the form:

```php
protected function createViewData(): array
{
    return [
        'departments' => Department::active()->orderBy('name')->get(),
    ];
}
```

**`beforeDestroy()`** — guard before deletion:

```php
protected function beforeDestroy(Model $record): void
{
    abort_if($record->posts()->exists(), 422, 'Cannot delete a category that has posts.');
}
```

**`store()` / `update()`** — always call `authorizeAction()`:

```php
public function store(StorePostCategoryRequest $request): RedirectResponse
{
    $this->authorizeAction('create');

    PostCategory::create($request->validated());

    return $this->successRedirect('created');
}

public function update(UpdatePostCategoryRequest $request, int|string $record): RedirectResponse
{
    $postCategory = $this->findRecord($record);
    $this->authorizeAction('update', $postCategory);

    $postCategory->update($request->validated());

    return $this->successRedirect('updated');
}
```

---

### Form Input Components

Use `@include` with the `layouts.form.inputs.*` partials. Each accepts a `$var` array.

```blade
{{-- Text / Number / Email / Date / etc. --}}
@include('layouts.form.inputs.text', ['var' => [
    'name'        => 'field_name',
    'label'       => 'Field Label',
    'value'       => $record?->field_name,
    'placeholder' => 'Enter value',
    'type'        => 'text',      // default; also: number, email, date, password ...
    'div'         => 'col-md-6',  // Bootstrap column class
    'required'    => true,
    'autofocus'   => true,
    'params'      => ['min' => '0', 'data-custom' => 'value'],
]])

{{-- Textarea --}}
@include('layouts.form.inputs.textarea', ['var' => [
    'name'  => 'description',
    'label' => 'Description',
    'value' => $record?->description,
    'rows'  => 3,
    'div'   => 'col-md-12',
]])

{{-- Select --}}
@include('layouts.form.inputs.select', ['var' => [
    'name'        => 'status',
    'label'       => 'Status',
    'value'       => $record?->is_active ?? 1,
    'options'     => [1 => 'Active', 0 => 'Inactive'],
    'prompt'      => 'Select status',
    'div'         => 'col-md-4',
    'required'    => true,
    'select2'     => true,
    'allow_clear' => true,
]])

{{-- File --}}
@include('layouts.form.inputs.file', ['var' => [
    'name'    => 'supporting_document',
    'label'   => 'Supporting Document',
    'accept'  => '.pdf,.doc,.docx',
    'div'     => 'col-md-12',
    'preview' => $record?->supporting_document
        ? Storage::disk('public')->url($record->supporting_document)
        : null,
]])
```

---

### Reusable Media File Input

For Spatie MediaLibrary uploads, use `layouts.form.inputs.media-file`.

```blade
{{-- Profile photo --}}
@include('layouts.form.inputs.media-file', ['var' => [
    'name'        => 'profile_picture',
    'label'       => 'Profile Picture',
    'type'        => 'image-circle',
    'media'       => $record?->getFirstMedia('profile_picture'),
    'preview_url' => $record?->getFirstMediaUrl('profile_picture', 'thumb'),
    'accept'      => 'image/jpeg,image/png,image/webp',
    'div'         => 'col-md-6',
]])

{{-- Single file --}}
@include('layouts.form.inputs.media-file', ['var' => [
    'name'   => 'resume',
    'label'  => 'Resume/CV',
    'type'   => 'file',
    'media'  => $record?->getFirstMedia('resume'),
    'accept' => '.pdf,application/pdf',
    'div'    => 'col-md-6',
]])

{{-- Multiple files --}}
@include('layouts.form.inputs.media-file', ['var' => [
    'name'     => 'certificates',
    'label'    => 'Certificates',
    'type'     => 'file',
    'media'    => $record?->getMedia('certificates'),
    'multiple' => true,
    'accept'   => '.pdf,application/pdf',
    'div'      => 'col-md-12',
]])
```

---

### Select2 Dropdowns

For AJAX/cascading selects, call `.trigger('change')` after appending options:

```javascript
$.getJSON(url, { country_id: countryId }, function (data) {
    $('#city_id').empty().append('<option value=""></option>');
    $.each(data, function (i, city) {
        $('#city_id').append(new Option(city.name, city.id));
    });
    $('#city_id').trigger('change');
});
```

---

### JavaScript Timing Pattern

Vite injects bundles as `type="module"` (deferred). Inline `@push('scripts')` blocks run before the module finishes loading. Use the polling-retry pattern for all inline scripts:

```javascript
function initMyModule() {
    if (typeof $ === 'undefined' || typeof bootstrap === 'undefined') {
        setTimeout(initMyModule, 100);
        return;
    }

    $(document).ready(function () {
        // safe to use $ and bootstrap here
    });
}

initMyModule();
```

---

## Customising Stubs

All stubs live in `stubs/crud-module/`. Edit them to change the default boilerplate for every future module.

```
stubs/crud-module/
├── migration.stub
├── model.stub
├── observer.stub
├── policy.stub
├── store-request.stub
├── update-request.stub
├── controller.stub
├── datatable-controller.stub
└── views/
    ├── index.blade.stub
    ├── form.blade.stub
    └── show.blade.stub
```

### Placeholder tokens

| Token | Example output |
|---|---|
| `{ModuleName}` | `PostCategory` |
| `{moduleName}` | `postCategory` |
| `{module-names}` | `post-categories` |
| `{module_names}` | `post_categories` |
| `{module_name}` | `post_category` |
| `{Module Name}` | `Post Category` |
| `{Module Names}` | `Post Categories` |
| `{module names}` | `post categories` |
| `{module name}` | `post category` |

Tokens work in PHP files, Blade files, and any other plain text file.
