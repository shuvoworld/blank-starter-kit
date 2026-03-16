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
  - [Layer 0 — Global Superuser Bypass](#layer-0--global-superuser-bypass)
  - [Layer 1 — Route Middleware](#layer-1--route-middleware)
  - [Layer 2 — Database Permissions](#layer-2--database-permissions)
  - [Layer 3 — Policy](#layer-3--policy)
  - [Layer 4 — Controller](#layer-4--controller)
  - [Layer 5 — DataTable Query Scoping](#layer-5--datatable-query-scoping)
  - [Layer 6 — Blade Views](#layer-6--blade-views)
  - [Checking Permissions in PHP (Any Context)](#checking-permissions-in-php-any-context)
  - [Seeded Permissions Reference](#seeded-permissions-reference)
  - [Common Mistakes](#common-mistakes)
- [Removing a Module](#removing-a-module)
- [Reference](#reference)
  - [Observer](#observer)
  - [DataTable Controller](#datatable-controller)
  - [Controller](#controller)
  - [Service Layer (Optional)](#service-layer-optional)
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
[ ] 4. Add validation rules to YourModuleRequest
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
| `BaseController` | Handles all CRUD actions (`index`, `create`, `store`, `edit`, `update`, `show`, `destroy`) with built-in authorization and flash messages |
| `BaseDataTableController` | Handles the server-side DataTables AJAX endpoint |
| `BasePolicy` | Provides Superuser bypass and default CRUD permission checks; extended by every module policy |

```
YourModuleController          → extends BaseController
YourModuleDataTableController → extends BaseDataTableController
YourModulePolicy              → extends BasePolicy
```

An optional service layer is available for modules that need post-create / post-update side-effects (e.g. syncing relationships):

```
YourModuleService             → extends BaseService   (optional)
```

Authorization flows through three layers in order:

```
Route Middleware → BaseController → Policy → Spatie Permission
```

---

## File Structure

The scaffold command generates these **10 files**:

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
            YourModuleRequest.php      ← single class handles create & update
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

A **single** `PostCategoryRequest` class handles both create and update. It extends `BaseFormRequest`, which provides `recordId()` — the route `{record}` value — and `isUpdating()` — true when a record ID is present.

**`app/Http/Requests/PostCategoryRequest.php`**

```php
class PostCategoryRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $id = $this->recordId();   // null on create, record ID on update

        return [
            'name'        => ['required', 'string', 'max:255', Rule::unique('post_categories', 'name')->ignore($id)],
            'slug'        => ['required', 'string', 'max:255', Rule::unique('post_categories', 'slug')->ignore($id)],
            'description' => ['nullable', 'string'],
            'is_active'   => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The category name is required.',
            'name.unique'   => 'This name is already in use.',
            'slug.unique'   => 'This slug is already in use.',
        ];
    }
}
```

**When some fields are required on create but optional on update**, use the `$sometimes` spread pattern:

```php
$id        = $this->recordId();
$sometimes = $id ? ['sometimes'] : [];

return [
    'name'  => [...$sometimes, 'required', 'string', 'max:255'],
    'price' => [...$sometimes, 'required', 'numeric', 'min:0'],
];
```

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

| Method | URI | Route name | Middleware permission |
|--------|-----|------------|-----------------------|
| GET | `/post-categories/datatable` | `post-categories.datatable` | `view any post-categories` |
| GET | `/post-categories` | `post-categories.index` | `view any post-categories` |
| GET | `/post-categories/create` | `post-categories.create` | `create post-categories` |
| POST | `/post-categories` | `post-categories.store` | `create post-categories` |
| GET | `/post-categories/{record}` | `post-categories.show` | `view any post-categories` |
| GET | `/post-categories/{record}/edit` | `post-categories.edit` | `update post-categories` |
| PUT | `/post-categories/{record}` | `post-categories.update` | `update post-categories` |
| DELETE | `/post-categories/{record}` | `post-categories.destroy` | `delete post-categories` |

> **Note:** The route middleware uses space-format permission names (`view any post-categories`). The policy and seeder use dot-notation (`post-categories.view`). Both checks run on every request: the middleware first (HTTP 403 if it fails), then the policy inside the controller. Superusers bypass both layers automatically via `Gate::before()`.

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

Authorization is enforced at **seven distinct points**. Every request passes through all that apply:

```
Layer 0 — Gate::before()       Global Superuser bypass (AppServiceProvider)
Layer 1 — Route Middleware      HTTP-level guard before the controller runs
Layer 2 — Database Permissions  The permission records that layers 1–6 check against
Layer 3 — Policy                Class-level and row-level authorization logic
Layer 4 — Controller            authorizeAction() / direct ownership guard
Layer 5 — DataTable query       Scopes what rows a user can see
Layer 6 — Blade views           Hides buttons and UI sections the user cannot use
```

A request to edit a record flows through all of them:

```
GET /post-categories/5/edit
  │
  ├─ [AUTH middleware]  — is the user logged in? NO → redirect to login
  │
  ├─ [Layer 1: permission middleware]
  │    HasPermission::handle() calls $user->can('update post-categories')
  │      → Gate::before() fires first — Superuser? return true immediately ✓
  │      → Otherwise: does user have the 'update post-categories' permission?
  │           NO  → 403, controller never runs
  │           YES ↓
  │
  ├─ [Layer 4: BaseController::edit()]
  │    $this->authorizeAction('update', $record)
  │      → PostCategoryPolicy::before()  — Superuser? return true ✓
  │      → PostCategoryPolicy::update()  — $user->can('post-categories.update')
  │           NO  → 403
  │           YES → view rendered
  │
  └─ [Layer 6: Blade view]
       @can('update', $postCategory) — controls Edit / Delete button visibility
```

> **Two permission name formats run on every request.** The route middleware uses a space format (`update post-categories`) while the policy checks use dot-notation (`post-categories.update`). Both must pass for a non-Superuser to succeed. Superusers skip both via `Gate::before()`.

---

### Layer 0 — Global Superuser Bypass

**File:** `app/Providers/AppServiceProvider.php`

```php
Gate::before(function (User $user, string $ability): ?bool {
    if ($user->hasRole('Superuser')) {
        return true;   // short-circuits every Gate, policy, and permission check
    }
    return null;       // null = continue to normal evaluation
});
```

`Gate::before()` runs before any policy method or `$user->can()` call. Returning `true` immediately grants the ability — no further checks run. Returning `null` tells Laravel to continue with normal evaluation.

**What this means in practice:**
- Superusers pass every route middleware, every policy, and every `@can` check automatically.
- `$user->can('anything')` → `true` for Superusers, regardless of assigned permissions.
- `$user->hasRole('Superuser')` bypasses the Gate and is the one safe place to check for the Superuser role.

> **Do not rely on `Gate::before()` as the only guard.** It fires for `$user->can()` but not for `$user->hasRole()` or `$user->hasPermissionTo()` called directly — use `$user->can()` everywhere except when you explicitly need to check a role (see [Checking Permissions in PHP](#checking-permissions-in-php-any-context)).

---

### Layer 1 — Route Middleware

**Files:** `app/Http/Middleware/HasPermission.php`, `app/Http/Middleware/HasRole.php`
**Registration:** `bootstrap/app.php` (aliases `permission` and `role`)

The middleware is the **first enforced gate**. If it fails, the controller never runs.

#### `permission:` middleware

```php
// Usage on a route:
->middleware('permission:update post-categories')

// What it does (HasPermission.php):
if (! auth()->user()->can($permission)) {
    abort(403);
}
```

`can()` goes through the Laravel Gate → `Gate::before()` fires → **Superuser bypass works correctly**.

#### `role:` middleware

```php
// Usage on a route:
->middleware('role:Admin')

// What it does (HasRole.php):
if (! auth()->user()->hasRole($role)) {
    abort(403);
}
```

`hasRole()` is a Spatie method that queries the database directly — it **does NOT go through the Gate**. The Superuser bypass in `Gate::before()` does **not** fire. A Superuser without the explicit role assigned would be blocked.

> **Always prefer `permission:` over `role:` middleware.** The `role:` middleware breaks the Superuser bypass and creates brittle role-name coupling. Reserve it for edge cases where you genuinely need to gate on role identity rather than capability.

#### Permission name format in route middleware

The `Route::crudModule()` macro derives permission names from the URL prefix by converting hyphens to spaces:

```
prefix: post-categories  →  view any post-categories / create post-categories / etc.
prefix: leave-requests   →  view any leave-requests  / approve leave requests / etc.
```

| Route action | Middleware applied |
|---|---|
| `index`, `datatable`, `show` | `permission:view any {prefix}` (group-level) |
| `create`, `store` | `permission:create {prefix}` |
| `edit`, `update` | `permission:update {prefix}` |
| `destroy` | `permission:delete {prefix}` |

These space-format names are **different from the dot-notation names** stored in the database (`post-categories.view`). Both must be granted to non-Superusers or the request will fail.

#### Ownership-only routes (no permission middleware)

Some routes are intentionally unprotected by permission middleware because they are scoped to the authenticated user's own data. Access is enforced in the controller with a direct ownership check:

```php
// routes/web.php — no permission middleware on this group
Route::prefix('my-leave-requests')->name('my-leave-requests.')->group(function () {
    Route::get('', [LeaveRequestController::class, 'myRequests'])->name('index');
    Route::post('/', [LeaveRequestController::class, 'store'])->name('store');
    // ...
});

// LeaveRequestController.php — ownership enforced in the method
public function showMy(int|string $record): View
{
    $leaveRequest = $this->findRecord($record);

    if ($leaveRequest->user_id !== auth()->id()) {
        abort(403);   // ownership guard — no policy involved
    }

    return view('leave-requests.show-my', compact('leaveRequest'));
}
```

Use this pattern when every authenticated user is entitled to access their own records regardless of role, and no admin-level permission gate is needed.

---

### Layer 2 — Database Permissions

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

### Layer 3 — Policy

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
    // (list is scoped in the DataTable query — see Layer 5)
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

### Layer 4 — Controller

#### What BaseController handles automatically

`BaseController` handles all eight standard CRUD actions. You never override `store()` or `update()` directly — use hooks instead.

| Inherited method | Ability enforced automatically |
|-----------------|-------------------------------|
| `index()` | `viewAny` |
| `create()` | `create` |
| `store()` | `create` |
| `edit()` | `update` |
| `update()` | `update` |
| `destroy()` | `delete` |

#### Wiring validation — `requestClass()` hook

Tell `BaseController` which Form Request to use by overriding `requestClass()`. The base `store()` and `update()` resolve it automatically, run validation, and call `create()` or `update()` on the model (or delegate to the service if one is set):

```php
protected function requestClass(): ?string
{
    return PostCategoryRequest::class;
}
```

This is the **only** thing needed for standard validation. Do not override `store()` or `update()` just to inject a typed Form Request — that causes a PHP fatal error (see Common Mistakes).

#### Passing data to views — `createViewData()` / `editViewData()`

```php
protected function createViewData(): array
{
    return [
        'departments' => Department::active()->orderBy('name')->get(),
    ];
}

protected function editViewData(Model $record): array
{
    $record->load('category');

    return [
        'departments' => Department::active()->orderBy('name')->get(),
    ];
}
```

#### Guarding deletion — `beforeDestroy()`

```php
protected function beforeDestroy(Model $record): void
{
    abort_if($record->posts()->exists(), 422, 'Cannot delete a category that has posts.');
}
```

#### Rendering a show view — `show()`

The base default redirects to `edit`. Override `show()` when you want a dedicated read-only page:

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

#### Quick reference: when to call `authorizeAction()` yourself

| Method | Call authorizeAction? | Notes |
|--------|-----------------------|-------|
| `store()` | **Never** | Base handles it — use `requestClass()` hook |
| `update()` | **Never** | Base handles it — use `requestClass()` hook |
| `show()` | **Only if overriding** | Base default redirects to edit (already authorized) |
| `approve()` / custom | **Yes** | Always, pass the record |
| `index()` | No | Base handles it |
| `create()` | No | Base handles it |
| `edit()` | No | Base handles it |
| `destroy()` | No | Base handles it |

#### Direct ownership guard (no policy)

For employee self-service actions where no admin-level permission is needed, skip `authorizeAction()` and do a direct ID check:

```php
public function showMy(int|string $record): View
{
    $leaveRequest = $this->findRecord($record);

    if ($leaveRequest->user_id !== auth()->id()) {
        abort(403);   // ownership guard — not routed through policy
    }

    return view('leave-requests.show-my', compact('leaveRequest'));
}
```

This is appropriate when:
- The route has no permission middleware (any authenticated user may reach it)
- The only valid access is ownership — there is no admin-override path
- You do not want a policy method for this action

> **Never call `$this->authorize()` directly.** Always use `$this->authorizeAction()`. It is the single authorization entry point for module controllers.

---

### Layer 5 — DataTable Query Scoping

When `viewAny` returns `true` for all users (Case B in Layer 3 — Policy), the list page loads for everyone — but the rows returned must be scoped. This is handled in the DataTable controller's `indexQuery()` method.

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

### Layer 6 — Blade Views

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
// Single permission check
$user->can('post-categories.approve');          // ✅ Gate fires, Superuser bypass works
$user->hasPermissionTo('post-categories.view'); // ❌ bypasses Gate

// Any of several permissions
$user->canAny(['leave-requests.approve', 'leave-requests.reject']); // ✅

// Role check — ONLY safe use of hasRole()
// Use this when you genuinely need role identity, not a capability check:
$user->hasRole('Superuser'); // ✅ safe — used in Gate::before() itself
$user->hasRole('Admin');     // ⚠️ does not respect Superuser bypass — prefer can()
```

#### Checking in a controller, service, or job

```php
$user = auth()->user();

// Single
if ($user->can('departments.delete')) {
    // ...
}

// Any of a set
if ($user->canAny(['leave-requests.approve', 'leave-requests.reject'])) {
    // show action buttons
}

// Current authenticated user shorthand
if (auth()->user()->can('products.create')) {
    // ...
}
```

#### Using RbacService

`App\Services\RbacService` wraps the above into injectable, testable methods. Inject it when you need permission or role checks inside a service class:

```php
class ReportService
{
    public function __construct(private RbacService $rbac) {}

    public function exportAll(User $user): void
    {
        if (! $this->rbac->userHasPermission($user, 'reports.export')) {
            abort(403);
        }
        // ...
    }
}
```

| Method | Equivalent |
|---|---|
| `userHasPermission($user, $perm)` | `$user->can($perm)` |
| `userHasAnyPermission($user, [...])` | `$user->canAny([...])` |
| `userHasAllPermissions($user, [...])` | all of `$user->can(...)` |
| `userHasRole($user, $role)` | `$user->hasRole($role)` |
| `isSuperuser($user)` | `$user->hasRole('Superuser')` |
| `getUserPermissions($user)` | `$user->getAllPermissions()` |
| `getUserPermissionsGrouped($user)` | permissions grouped by module |

> `RbacService::userHasPermission()` delegates to `$user->can()`, so the Superuser bypass fires correctly. `userHasRole()` delegates to `$user->hasRole()` — it does not fire the bypass, which is intentional for role-identity checks.

---

### Seeded Permissions Reference

All permissions are created in `database/seeders/RbacSeeder.php` using `{module}.{action}` naming. This is the complete inventory:

| Module | Permissions |
|--------|------------|
| `users` | `view` `create` `update` `delete` `manage-roles` |
| `roles` | `view` `create` `update` `delete` |
| `permissions` | `view` `update` |
| `employees` | `view` `create` `update` `delete` |
| `departments` | `view` `create` `update` `delete` |
| `designations` | `view` `create` `update` `delete` |
| `employee-categories` | `view` `create` `update` `delete` |
| `leave-types` | `view` `create` `update` `delete` |
| `leave-requests` | `view` `create` `update` `delete` `approve` `reject` `cancel` |
| `leave-balances` | `view` `create` `update` `delete` |
| `holidays` | `view` `create` `update` `delete` |
| `schedules` | `view` `create` `update` `delete` `assign` |
| `shifts` | `view` `create` `update` `delete` |
| `products` | `view` `create` `update` `delete` |
| `landing-page-sections` | `view` `create` `update` `delete` |
| `dashboard` | `view` |
| `activity-log` | `view` |

**Roles and their permission scope:**

| Role | Scope |
|------|-------|
| **Superuser** | All permissions (plus `Gate::before()` bypass) |
| **Admin** | All permissions except `users.delete` and `roles.delete` |
| **Employee** | `dashboard.view`, `employees.view`, `departments.view`, `designations.view`, `holidays.view`, `leave-requests.create`, `leave-balances.view` |

---

### Common Mistakes

**1. Overriding `store()` or `update()` with a typed Form Request parameter**

PHP's method signature rules do not allow narrowing a parameter type in an override. This causes a fatal `Declaration must be compatible` error at runtime.

```php
// ❌ Fatal error — narrows Request to StorePostCategoryRequest
public function store(StorePostCategoryRequest $request): RedirectResponse { ... }

// ✅ Correct — use the requestClass() hook instead
protected function requestClass(): ?string
{
    return PostCategoryRequest::class;
}
```

**2. Forgetting `requestClass()` entirely**

Without `requestClass()`, `BaseController::store()` and `update()` fall back to the raw `Request` with no validation. Your data is saved unvalidated.

**3. Using `$this->authorize()` instead of `$this->authorizeAction()`**

```php
// ❌ Wrong
$this->authorize('create', PostCategory::class);

// ✅ Correct
$this->authorizeAction('create');
```

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

**7. Using `role:` middleware when you need Superuser bypass**

The `role:` middleware calls `$user->hasRole()` which bypasses the Laravel Gate. A Superuser user without the specific role explicitly assigned will get a 403.

```php
// ❌ Wrong — blocks Superusers who don't have the 'Admin' role assigned
->middleware('role:Admin')

// ✅ Correct — create a permission and use permission: middleware instead
->middleware('permission:reports.view')
```

Only use `role:` middleware when you explicitly need to gate on role identity and do not want Superuser bypass (rare — e.g., restricting access to a Superuser-only setup screen).

**8. Not marking virtual/aggregate columns as non-searchable in DataTable**

Computed columns (e.g. `withCount()` aggregates) are not real database columns. If you include them in `tableColumns()` without `'searchable' => false`, Yajra will attempt a SQL `LIKE` search on them and throw a column-not-found error.

```php
// ❌ Wrong — roles_count is a withCount() aggregate, not a real column
['data' => 'roles_count', 'name' => 'roles_count', 'label' => 'Roles'],

// ✅ Correct
['data' => 'roles_count', 'name' => 'roles_count', 'label' => 'Roles', 'searchable' => false, 'orderable' => false],
```

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

#### Wiring validation

Point `BaseController` to the request class via `requestClass()`. This is the **only** hook needed for standard CRUD:

```php
protected function requestClass(): ?string
{
    return PostCategoryRequest::class;
}
```

`BaseController::store()` resolves this class, validates, and calls `PostCategory::create($data)`.
`BaseController::update()` resolves this class, validates, and calls `$record->update($data)`.

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

**`fileFields()`** — declare file upload fields with their storage paths:

```php
protected function fileFields(): array
{
    return [
        'supporting_document' => 'post-categories/documents',
    ];
}
```

File upload and cleanup (on update and delete) are handled automatically by `BaseController`.

**`beforeDestroy()`** — guard before deletion:

```php
protected function beforeDestroy(Model $record): void
{
    abort_if($record->posts()->exists(), 422, 'Cannot delete a category that has posts.');
}
```

---

### Service Layer (Optional)

Use a service when `BaseController`'s built-in `create()` / `update()` is not enough — for example, when you need to sync a relationship or run side-effects after saving.

#### Creating a service

```php
// app/Services/PostCategoryService.php
class PostCategoryService extends BaseService
{
    protected string $modelClass = PostCategory::class;

    protected function afterCreate(Model $record, array $data): void
    {
        // sync tags, fire events, send notifications, etc.
        if (! empty($data['tag_ids'])) {
            $record->tags()->sync($data['tag_ids']);
        }
    }

    protected function afterUpdate(Model $record, array $data): void
    {
        if (array_key_exists('tag_ids', $data)) {
            $record->tags()->sync($data['tag_ids'] ?? []);
        }
    }
}
```

#### Available hooks (all optional, all no-op by default)

| Hook | Signature | When it runs |
|------|-----------|--------------|
| `beforeCreate` | `(array $data): void` | Before `Model::create()` |
| `afterCreate` | `(Model $record, array $data): void` | After record is created |
| `beforeUpdate` | `(Model $record, array $data): void` | Before `$record->update()` |
| `afterUpdate` | `(Model $record, array $data): void` | After record is updated |
| `beforeDelete` | `(Model $record): void` | Before `$record->delete()` |
| `afterDelete` | `(Model $record): void` | After record is deleted |

#### Wiring the service to the controller

Inject the service in the constructor and assign it to `$this->service`. `BaseController` will automatically delegate `store()` and `update()` to the service instead of acting directly on the model.

```php
public function __construct(
    PostCategoryDataTableController $dataTableController,
    PostCategoryService $service,
) {
    $this->model = PostCategory::class;
    $this->routePrefix = 'post-categories';
    $this->viewPrefix = 'post-categories';
    $this->resourceName = 'Post Category';
    $this->dataTableController = $dataTableController;
    $this->service = $service;
}
```

#### Filtering data before update

Override `update()` in the service to strip fields that should not be mass-assigned on update. The most common case is an optional password field:

```php
public function update(Model $record, array $data): Model
{
    if (empty($data['password'])) {
        unset($data['password']);
    }

    return parent::update($record, $data);
}
```

> `$data` is passed by value in the hook signatures (`beforeUpdate`, `afterUpdate`), so modifications inside those hooks do not affect the data saved by `$record->update()`. Override `update()` itself when you need to filter `$data` before it reaches the model.

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
├── request.stub              ← single merged request (create + update)
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
