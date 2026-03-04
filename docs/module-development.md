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
  - [7. Add a Sidebar Link](#7-add-a-sidebar-link)
  - [8. Customise the Views](#8-customise-the-views)
- [Removing a Module](#removing-a-module)
- [Reference](#reference)
  - [Observer](#observer)
  - [Policy](#policy)
  - [Authorization Notes](#authorization-notes)
  - [DataTable Controller](#datatable-controller)
  - [Controller](#controller)
  - [Form Input Components](#form-input-components)
  - [Reusable Media File Input](#reusable-media-file-input)
  - [Select2 Dropdowns](#select2-dropdowns)
  - [JavaScript Timing Pattern](#javascript-timing-pattern)
- [Customising Stubs](#customising-stubs)

---

## Quick Start Checklist

Use this as a task list when creating a new module. Steps 1–7 are **required** for the module to be fully functional.

```
[ ] 1. php artisan make:crud-module YourModuleName
[ ] 2. Add columns to the generated migration → php artisan migrate
       (permissions are created automatically by the migration)
[ ] 3. Add columns to $fillable (and casts if needed) in the Model
[ ] 4. Add validation rules to StoreYourModuleRequest and UpdateYourModuleRequest
[ ] 5. Add Route::crudModule(...) to routes/web.php
[ ] 6. Add a sidebar link
[ ] 7. Assign permissions to roles (via Roles UI or role seeder)
[ ] 8. Customise views (add form fields, table columns, show page)
```

---

## Architecture Overview

Every module is built on two base classes:

| Class | Purpose |
|---|---|
| `BaseController` | Handles `index`, `create`, `edit`, `show`, `destroy` with hooks for auth and flash messages |
| `BaseDataTableController` | Handles the server-side DataTables AJAX endpoint |

```
YourModuleController          → extends BaseController
YourModuleDataTableController → extends BaseDataTableController
```

---

## File Structure

The scaffold command generates these 11 files:

```
database/migrations/
    YYYY_MM_DD_HHMMSS_create_{module_names}_table.php

app/
    Models/
        YourModule.php
    Observers/
        YourModuleObserver.php
    Policies/
        YourModulePolicy.php
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

The generated migration includes the standard audit columns. Add your module-specific columns in the gap shown:

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
```

Then run:

```bash
php artisan migrate
```

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

This single call registers all 8 routes and wires up permission middleware automatically:

| Method | URI | Route name | Permission required |
|--------|-----|------------|---------------------|
| GET | `/post-categories/datatable` | `post-categories.datatable` | `view any post categories` |
| GET | `/post-categories` | `post-categories.index` | `view any post categories` |
| GET | `/post-categories/create` | `post-categories.create` | `create post categories` |
| POST | `/post-categories` | `post-categories.store` | `create post categories` |
| GET | `/post-categories/{record}` | `post-categories.show` | `view any post categories` |
| GET | `/post-categories/{record}/edit` | `post-categories.edit` | `update post categories` |
| PUT | `/post-categories/{record}` | `post-categories.update` | `update post categories` |
| DELETE | `/post-categories/{record}` | `post-categories.destroy` | `delete post categories` |

The permission slug is derived from the prefix: `post-categories` → `post categories`.

**Need extra routes?** Pass a closure as the fourth argument:

```php
Route::crudModule('post-categories', PostCategoryController::class, PostCategoryDataTableController::class, function () {
    Route::post('/{record}/publish', [PostCategoryController::class, 'publish'])
        ->name('publish')
        ->middleware('permission:update post categories');
});
```

---

### 6. Permissions (Automatic)

No manual seeding required. The generated migration creates all four permissions automatically when you run `php artisan migrate`:

```
view any post categories
create post categories
update post categories
delete post categories
```

The migration's `down()` method removes them if rolled back.

The permission slug is the module's human-readable plural name in lowercase (hyphens become spaces): `post-categories` → `post categories`.

**Assigning permissions to roles** is the only manual step — do this in your role seeder or via the Roles UI in the application. Superusers bypass all permission checks globally via `Gate::before()` in `AppServiceProvider` and do not need permissions assigned explicitly.

---

### 7. Add a Sidebar Link

**File:** `resources/views/layouts/dashboard/partials/sidebar.blade.php`

The sidebar uses a priority variable to show links based on role level:

```blade
@if($priority >= 3)   {{-- Superuser --}}
@elseif($priority >= 2) {{-- Admin --}}
@else                 {{-- Employee --}}
```

Add your link inside the appropriate block:

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

### 8. Customise the Views

At this point the module is fully functional with a basic name-only form. Customise the three generated views to match your columns.

#### form.blade.php

Add `@include` calls for each field using the form input partials. See [Form Input Components](#form-input-components) for all available inputs.

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

    @include('layouts.form.inputs.text', ['var' => [
        'name'        => 'slug',
        'label'       => 'Slug',
        'value'       => $record?->slug,
        'placeholder' => 'e.g., technology',
        'div'         => 'col-md-6',
        'required'    => true,
    ]])
</div>

<div class="row">
    @include('layouts.form.inputs.textarea', ['var' => [
        'name'  => 'description',
        'label' => 'Description',
        'value' => $record?->description,
        'rows'  => 3,
        'div'   => 'col-md-12',
    ]])
</div>

<div class="row">
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

#### index.blade.php — table columns

Update `tableColumns()` in `PostCategoryDataTableController` to match the columns you want in the table. See [DataTable Controller](#datatable-controller) for full details.

#### show.blade.php

Add rows to the detail table for each field you want to display on the show page.

---

## Removing a Module

Use the `remove:crud-module` command to delete all artifacts created by `make:crud-module`:

```bash
php artisan remove:crud-module PostCategory
```

Before deleting anything the command prints an inventory of what it found:

```
Inventory for PostCategory:
  [DELETE]  app/Models/PostCategory.php
  [DELETE]  app/Observers/PostCategoryObserver.php
  [DELETE]  app/Policies/PostCategoryPolicy.php
  [DELETE]  app/Http/Requests/StorePostCategoryRequest.php
  [DELETE]  app/Http/Requests/UpdatePostCategoryRequest.php
  [DELETE]  app/Http/Controllers/PostCategoryController.php
  [DELETE]  app/Http/Controllers/PostCategoryDataTableController.php
  [DELETE]  resources/views/post-categories/  (directory)
  [DROP]    database table: post_categories
  [DELETE]  4 permissions: view any post categories, create post categories, ...
  [SKIP]    migration file not found (already rolled back or never created)
```

Add `--force` to skip the confirmation prompt:

```bash
php artisan remove:crud-module PostCategory --force
```

After the command runs, you must manually remove three things the command cannot safely touch:

1. **Route line** in `routes/web.php` — remove the `Route::crudModule(...)` call and its `use` imports
2. **Sidebar link** in `resources/views/layouts/dashboard/partials/sidebar.blade.php`
3. **Any references** to the module in seeders, other controllers, or existing tests

> **Tip:** Run `php artisan migrate:rollback` **before** `remove:crud-module` if you want the migration's `down()` method to handle permission cleanup — the remove command also deletes permissions, so either order works.

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

    // Example: cancel related records
    $postCategory->posts()->update(['status' => 'draft']);
}
```

---

### Policy

**File:** `app/Policies/PostCategoryPolicy.php`

Each ability checks a named permission using `$user->can()`, which routes through the Gate and respects the global Superuser bypass:

```php
public function viewAny(User $user): bool     { return $user->can('view any post categories'); }
public function view(User $user, ...): bool   { return $user->can('view any post categories'); }
public function create(User $user): bool      { return $user->can('create post categories'); }
public function update(User $user, ...): bool { return $user->can('update post categories'); }
public function delete(User $user, ...): bool { return $user->can('delete post categories'); }
```

> **Superuser bypass** — `Gate::before()` in `AppServiceProvider` returns `true` for any user with the `Superuser` role, short-circuiting every policy, gate, and permission middleware check. There is no per-policy `before()` hook needed.

**Policy auto-discovery** is on by default in Laravel 12. If it does not resolve automatically, register it manually in a service provider:

```php
use Illuminate\Support\Facades\Gate;

Gate::policy(PostCategory::class, PostCategoryPolicy::class);
```

---

### Authorization Notes

**Always use `can()` / `canAny()` — never call Spatie methods directly.**

Spatie provides low-level methods (`hasPermissionTo()`, `hasAnyPermission()`, `hasAllPermissions()`) that query its permission tables directly, bypassing the Laravel Gate entirely. The `Gate::before()` Superuser bypass only fires when code goes through the Gate.

| Use this | Not this |
|---|---|
| `$user->can('view any posts')` | `$user->hasPermissionTo('view any posts')` |
| `$user->canAny(['create posts', 'update posts'])` | `$user->hasAnyPermission([...])` |
| `collect($perms)->every(fn($p) => $user->can($p))` | `$user->hasAllPermissions([...])` |

This applies everywhere: controllers, middleware, policies, services, blade `@can` directives.

`@can` and `@canany` in Blade already go through the Gate — they are safe to use as-is.

---

### DataTable Controller

**File:** `app/Http/Controllers/PostCategoryDataTableController.php`

#### Constructor — required setup

```php
public function __construct()
{
    $this->model = PostCategory::class;
    $this->routePrefix = 'post-categories';
    $this->rawColumns = ['status_badge']; // list any HTML columns here; 'action' is always included
}
```

#### `indexQuery()` — base query

```php
protected function indexQuery(): Builder
{
    return PostCategory::query()
        ->with(['updatedBy'])
        ->orderBy('name');
}
```

Support filter inputs from the index page via request parameters:

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
        'updated_by_name' => fn (PostCategory $r) => $r->updatedBy?->name ?? '—',
        'status_badge'    => fn (PostCategory $r) => $r->is_active
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
        ['data' => 'DT_RowIndex',    'name' => 'DT_RowIndex', 'label' => '#',           'width' => '50', 'orderable' => false, 'searchable' => false, 'className' => 'text-center'],
        ['data' => 'name',           'name' => 'name',        'label' => 'Name'],
        ['data' => 'status_badge',   'name' => 'is_active',   'label' => 'Status',       'orderable' => false, 'searchable' => false, 'className' => 'text-center'],
        ['data' => 'updated_at',     'name' => 'updated_at',  'label' => 'Last Updated', 'orderable' => true,  'searchable' => false, 'className' => 'text-center'],
        ['data' => 'updated_by_name','name' => 'updated_by',  'label' => 'Updated By',   'orderable' => false, 'searchable' => false],
        ['data' => 'action',         'name' => 'action',      'label' => 'Actions',      'width' => '180', 'orderable' => false, 'searchable' => false, 'className' => 'text-center'],
    ];
}
```

#### `actionColumn()` — override to customise buttons

> **Important:** Do **not** type-hint the parameter. The base class declares `actionColumn($record): string` without a type, and PHP does not allow narrowing parameter types in overrides. Adding a type hint causes a fatal `Declaration must be compatible` error.

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

The constructor wires up `BaseController`. No changes required unless you need custom store/update logic.

```php
public function __construct(PostCategoryDataTableController $dataTableController)
{
    $this->model = PostCategory::class;
    $this->routePrefix = 'post-categories';
    $this->viewPrefix = 'post-categories';
    $this->resourceName = 'Post Category';
    $this->dataTableController = $dataTableController;
}
```

#### Available hooks

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

**`store()` / `update()`** — override for file uploads or relation syncing:

```php
public function store(StorePostCategoryRequest $request): RedirectResponse
{
    $this->authorize('create', PostCategory::class);

    $data = $request->validated();

    if ($request->hasFile('thumbnail')) {
        $data['thumbnail'] = $request->file('thumbnail')->store('post-categories', 'public');
    }

    PostCategory::create($data);

    return $this->successRedirect('created');
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
    'disabled'    => false,
    'tooltip'     => 'Help text shown on hover',
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
    'prompt'      => 'Select status',  // blank first option
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
    'tooltip' => 'Max 5MB.',
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

| `type` value | Behaviour |
|---|---|
| `image-circle` | Round 80px thumbnail with person-fill fallback |
| `image` | Rectangular thumbnail with image-fill fallback |
| `file` | Dismissible alert (single) or badge links (multiple) |

**Controller — handling uploads:**

```php
if ($request->hasFile('profile_picture')) {
    $record->clearMediaCollection('profile_picture');
    $record->addMediaFromRequest('profile_picture')->toMediaCollection('profile_picture');
}
```

**Model — defining collections:**

```php
public function registerMediaCollections(): void
{
    $this->addMediaCollection('profile_picture')->singleFile();
}

public function registerMediaConversions(?Media $media = null): void
{
    $this->addMediaConversion('thumb')->width(200)->height(200);
}
```

---

### Select2 Dropdowns

Any `<select>` rendered by the `layouts.form.inputs.select` partial with `'select2' => true` is automatically enhanced.

For raw selects, add `data-toggle="select2"` and always include a blank first option:

```blade
<select name="country_id" class="form-select" data-toggle="select2" data-placeholder="Select country" data-allow-clear="true">
    <option value=""></option>
    @foreach($countries as $country)
        <option value="{{ $country->id }}" {{ $record?->country_id == $country->id ? 'selected' : '' }}>
            {{ $country->name }}
        </option>
    @endforeach
</select>
```

For AJAX/cascading selects, call `.trigger('change')` after appending options so Select2 updates:

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

Vite injects bundles as `type="module"` (deferred). Inline `@push('scripts')` blocks run before the module finishes loading, so `$` and `bootstrap` are not yet defined. **Do not** use `$(document).ready(...)` directly.

Use the polling-retry pattern for all inline scripts:

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

The generated `index.blade.php` already uses this pattern for DataTables. Apply the same to any other inline scripts (cascade dropdowns, custom event handlers, etc.).

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
