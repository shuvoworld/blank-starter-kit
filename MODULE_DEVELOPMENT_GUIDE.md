# Module Development Guide

This document defines the standard architecture every module in this application must follow.
The **LeaveType** module is the current reference implementation of this pattern.

---

## Table of Contents

1. [Module Architecture Overview](#1-module-architecture-overview)
2. [Request & Permission Flow](#2-request--permission-flow)
3. [File Structure Per Module](#3-file-structure-per-module)
4. [Layer Responsibilities](#4-layer-responsibilities)
5. [BaseController & BaseDataTableController](#5-basecontroller--basedatatablecontroller)
6. [Permission Naming Convention](#6-permission-naming-convention)
7. [Creating a New Module — Checklist](#7-creating-a-new-module--checklist)
8. [File Upload Modules](#8-file-upload-modules)

---

## 1. Module Architecture Overview

Every module is composed of these layers, each with a single clear responsibility:

```
┌─────────────────────────────────────────────────────────┐
│                     HTTP Request                        │
└────────────────────────┬────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────┐
│  ROUTE MIDDLEWARE  (routes/web.php)                     │
│  permission:view any {module}                           │
│  Coarse gate — "Does this role have access at all?"     │
└────────────────────────┬────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────┐
│  FORM REQUEST  (app/Http/Requests/)                     │
│  Store{Model}Request / Update{Model}Request             │
│  Validates input before it reaches the controller       │
└────────────────────────┬────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────┐
│  CONTROLLER  (app/Http/Controllers/)                    │
│  Extends BaseController — inherits index/create/edit/   │
│  destroy. Overrides store/update/show as needed.        │
│  Paired with a dedicated *DataTableController.          │
└────────────────────────┬────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────┐
│  MODEL  (app/Models/)                                   │
│  Fillable, casts(), scopes, relationships               │
│  HasActivityLog trait — audit trail is automatic        │
└─────────────────────────────────────────────────────────┘
```

---

## 2. Request & Permission Flow

### Step-by-step for a typical `update` action

| Step | Layer | What happens |
|------|-------|--------------|
| 1 | Route middleware | Checks `permission:update {module}` on the edit/update routes — aborts 403 if the user lacks it |
| 2 | BaseController `edit()` | Calls `$this->authorizeAction('update', $record)` — override hook, no-op by default |
| 3 | Form Request `authorize()` | Always returns `true` — route middleware already guards access |
| 4 | Form Request `rules()` | Validates the submitted data |
| 5 | Controller `update()` | Calls `$record->update($request->validated())`, then `successRedirect()` |
| 6 | HasActivityLog trait | Automatically records the change to the activity log |

### Two types of authorization checks

| Type | Where | Example |
|------|-------|---------|
| **Role-based** | Route middleware | `permission:update leave types` on the PUT route |
| **Row-level** | `authorizeAction()` override | Override in child controller to add record-level checks |

Route middleware is the primary authorization mechanism. The `authorizeAction()` hook in `BaseController` is provided for row-level logic (e.g. "own records only") but is a no-op by default.

---

## 3. File Structure Per Module

Using `LeaveType` as the reference example:

```
app/
  Models/
    LeaveType.php                        — Fillable, casts(), scopes, relationships
                                           Uses: HasFactory, HasActivityLog
  Http/
    Controllers/
      LeaveTypeController.php            — Extends BaseController
                                           Overrides: show(), store(), update()
                                           Inherits: index(), create(), edit(), destroy()
      LeaveTypeDataTableController.php   — Extends BaseDataTableController
                                           Defines: dataTableColumns(), tableColumns(),
                                           indexQuery(), actionColumn()
    Requests/
      StoreLeaveTypeRequest.php          — rules() + messages() for create
      UpdateLeaveTypeRequest.php         — same rules, unique-ignore-self via {record}

database/
  migrations/
    TIMESTAMP_create_leave_types_table.php
  seeders/
    LeaveTypeSeeder.php                  — firstOrCreate() for idempotent seeding

resources/views/leave-types/
  index.blade.php                        — DataTable listing, Create button
  form.blade.php                         — Shared create / edit form ($editing boolean)
  show.blade.php                         — Read-only detail view

tests/Feature/
  LeaveTypeTest.php                      — CRUD tests + permission denial tests

routes/
  web.php                                — Permission-protected route group
```

> **No Policy, no Observer, no Factory** unless the module has row-level ownership rules,
> cascade side-effects, or complex seeding needs.

---

## 4. Layer Responsibilities

### Model (`app/Models/`)

- Defines `$fillable`, `casts()` method, relationships, and query scopes
- Uses `HasActivityLog` trait — all create/update/delete events are logged automatically; no Observer needed for auditing
- Uses `HasFactory` trait when a factory is required
- Does **not** contain authorization or HTTP logic

### Controller (`app/Http/Controllers/`)

Extends `BaseController`. Only override what is specific to the module:

| Scenario | Override |
|----------|----------|
| `show()` needs a dedicated view (not just a redirect to edit) | Override `show()` |
| `store()` / `update()` have specific logic (create, update, redirect) | Override both |
| The create form needs extra view data (dropdown options, etc.) | Override `createViewData()` |
| The edit form needs extra view data | Override `editViewData()` |
| A record must be guarded before deletion | Override `beforeDestroy()` |
| Post-delete cleanup is needed | Override `afterDestroy()` |

Returns `View` for page renders, `JsonResponse` for AJAX responses, `RedirectResponse` after writes.

### DataTable Controller (`app/Http/Controllers/`)

Extends `BaseDataTableController`. Handles the AJAX-only `/{module}/datatable` endpoint.

| Method | Purpose |
|--------|---------|
| `indexQuery()` | Base Eloquent query (ordering, eager loads, scopes) |
| `dataTableColumns()` | Map of `column_key => fn($record) => string` for computed/HTML columns |
| `tableColumns()` | Column definitions array sent to the frontend (header labels, widths, orderable flags) |
| `actionColumn()` | Override to add a View button or other custom actions alongside Edit / Delete |

Always declare `$rawColumns` with any column keys that output HTML.

### Form Request (`app/Http/Requests/`)

- `authorize()` always returns `true` — route middleware handles access
- `rules()` contains all validation as arrays of rule strings
- `UpdateRequest` reads `$this->route('record')` to exclude the current record from unique checks
- `messages()` provides human-readable errors per field

### Views (`resources/views/{module}/`)

| File | Purpose |
|------|---------|
| `index.blade.php` | Renders the DataTable. Passes `tableColumns` (PHP, for `<thead>`) and `dtColumns` (JSON, for JS config) |
| `form.blade.php` | Shared for create and edit. Uses `$editing` boolean and `$record` (null on create) |
| `show.blade.php` | Read-only detail. Edit/Delete buttons gated with `@can` |

Use `@include('layouts.form.inputs.*')` partials for all form fields.
For Select2-enhanced selects, pass `'select2' => true` — the partial renders `data-toggle="select2"` and the global JS initialiser picks it up.

### Seeder (`database/seeders/`)

Use `Model::firstOrCreate(['code' => $row['code']], $row)` so re-running the seeder is safe.

### Routes (`routes/web.php`)

```php
Route::prefix('{route-prefix}')
    ->name('{route-prefix}.')
    ->middleware('permission:view any {permission name}')
    ->group(function () {
        // DataTable AJAX — must be BEFORE /{record} to avoid being matched as an ID
        Route::get('/datatable', [ModuleDataTableController::class, 'datatable'])->name('datatable');

        Route::get('/', [ModuleController::class, 'index'])->name('index');
        Route::get('/create', [ModuleController::class, 'create'])->name('create')
            ->middleware('permission:create {permission name}');
        Route::post('/', [ModuleController::class, 'store'])->name('store')
            ->middleware('permission:create {permission name}');
        Route::get('/{record}', [ModuleController::class, 'show'])->name('show');
        Route::get('/{record}/edit', [ModuleController::class, 'edit'])->name('edit')
            ->middleware('permission:update {permission name}');
        Route::put('/{record}', [ModuleController::class, 'update'])->name('update')
            ->middleware('permission:update {permission name}');
        Route::delete('/{record}', [ModuleController::class, 'destroy'])->name('destroy')
            ->middleware('permission:delete {permission name}');
    });
```

> The route parameter is always `{record}`. `BaseController::findRecord()` resolves it via `Model::findOrFail($id)`.
> The `/datatable` route **must** come before `/{record}` or Laravel will match the string `"datatable"` as a record ID.

---

## 5. BaseController & BaseDataTableController

### `BaseController` — provided CRUD methods

| Method | Behaviour | Override? |
|--------|-----------|-----------|
| `index()` | Passes `tableColumns` + `dtColumns` to `{viewPrefix}.index` | Rarely |
| `create()` | Renders `{viewPrefix}.form` with `editing=false, record=null` + `createViewData()` | No — override `createViewData()` |
| `edit()` | Fetches record, renders `{viewPrefix}.form` with `editing=true` + `editViewData()` | No — override `editViewData()` |
| `show()` | Default: redirects to edit. Override to render a dedicated detail view. | Yes, when a show view exists |
| `destroy()` | Fetches record, fires `beforeDestroy()`, deletes, fires `afterDestroy()`. AJAX-aware (returns JSON or redirect) | No — override hooks |
| `successRedirect()` | Redirects to `{routePrefix}.index` with a flash `status` message | No |
| `authorizeAction()` | No-op hook. Override to add `$this->authorize()` / `abort_if()` checks | Yes, when row-level checks are needed |

### `BaseDataTableController` — provided methods

| Method | Behaviour | Override? |
|--------|-----------|-----------|
| `datatable()` | Validates AJAX, runs `indexQuery()`, adds custom columns, action column, filters, raw columns — returns JSON | No |
| `indexQuery()` | Default: `Model::query()->with($withRelations)` | Yes — add ordering, scopes, eager loads |
| `dataTableColumns()` | Default: empty. Return `['col_key' => fn($r) => string]` map | Yes — for badge/HTML columns |
| `tableColumns()` | Default: `#`, `name`, `is_active`, `action`. Override to define module-specific columns | Yes |
| `actionColumn()` | Default: Edit + Delete buttons. Override to add View or other buttons | Yes |
| `applyFilters()` | No-op hook. Override to add search/date range/status filters | Yes, when column filters exist |
| `authorizeDataTable()` | No-op hook. Override to gate the AJAX endpoint | Rarely |

### Constructor wiring

Every child controller must set these properties in `__construct()`:

```php
// In ModuleController:
public function __construct(ModuleDataTableController $dataTableController)
{
    $this->model              = Module::class;
    $this->routePrefix        = 'modules';
    $this->viewPrefix         = 'modules';
    $this->resourceName       = 'Module';
    $this->dataTableController = $dataTableController;
}

// In ModuleDataTableController:
public function __construct()
{
    $this->model       = Module::class;
    $this->routePrefix = 'modules';
    $this->rawColumns  = ['status_badge', 'other_html_column'];
}
```

---

## 6. Permission Naming Convention

| Action | Permission string | Middleware alias |
|--------|-------------------|-----------------|
| List / view any | `view any {module}` | `permission:view any {module}` |
| Create | `create {module}` | `permission:create {module}` |
| Update | `update {module}` | `permission:update {module}` |
| Delete | `delete {module}` | `permission:delete {module}` |

The `{module}` segment is the **lowercase, space-separated plural** of the model name:

| Model | Permission string | Route prefix |
|-------|-------------------|--------------|
| `Department` | `departments` | `departments` |
| `LeaveType` | `leave types` | `leave-types` |
| `BlogPost` | `blog posts` | `blog-posts` |

Permission strings use **spaces** (`leave types`); route prefixes use **hyphens** (`leave-types`).

Permission records must exist in the database. Add new permissions to `database/seeders/RbacSeeder.php`.

---

## 7. Creating a New Module — Checklist

Follow this order to avoid dependency issues:

- [ ] **Migration** — define columns, run `php artisan migrate`
- [ ] **Model** — `$fillable`, `casts()`, scopes, relationships; add `HasActivityLog` and `HasFactory` traits
- [ ] **Seeder** — use `firstOrCreate()` for idempotent seeding; add to `DatabaseSeeder` if needed
- [ ] **StoreRequest** — `rules()` + `messages()`
- [ ] **UpdateRequest** — same rules with `unique:table,col,$this->route('record')` ignore-self
- [ ] **DataTable Controller** — extend `BaseDataTableController`; implement `indexQuery()`, `dataTableColumns()`, `tableColumns()`, and `actionColumn()` if a View button is needed
- [ ] **Controller** — extend `BaseController`; wire constructor properties; override only `show()`, `store()`, `update()`, and any data/hook methods needed
- [ ] **Views** — `index.blade.php`, `form.blade.php` (shared, with `$editing` flag), `show.blade.php`
- [ ] **Routes** — add permission-protected group to `routes/web.php`; `/datatable` before `/{record}`
- [ ] **Permissions** — add four permission records to `RbacSeeder`
- [ ] **Test** — feature test covering create, update, delete success paths and 403 denial cases

---

## 8. File Upload Modules

If a module handles file uploads, additionally:

- Implement `HasMedia` interface and use `InteractsWithMedia` trait on the Model
- Define collections in `registerMediaCollections()` — set `singleFile()` for avatars
- Define conversions in `registerMediaConversions()` — generate `thumb` and `medium` sizes for images
- Add file validation rules to both `StoreRequest` and `UpdateRequest`
- Add `enctype="multipart/form-data"` to the form tag in views
- In `store()`: upload files after `Model::create()`
- In `update()`: call `clearMediaCollection()` before re-uploading single-file collections
- In `destroy()`: Spatie automatically deletes associated media when the model is deleted

Reference implementation: `Employee` model, `EmployeeController`, `StoreEmployeeRequest`.
