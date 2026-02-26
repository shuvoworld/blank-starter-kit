# Module Development Guide

This document defines the standard architecture every module in this application must follow.
All existing modules (Department, Designation, Product, Employee) implement this pattern and serve as reference.

---

## Table of Contents

1. [Module Architecture Overview](#1-module-architecture-overview)
2. [Request & Permission Flow](#2-request--permission-flow)
3. [File Structure Per Module](#3-file-structure-per-module)
4. [Layer Responsibilities](#4-layer-responsibilities)
5. [Permission Naming Convention](#5-permission-naming-convention)
6. [Creating a New Module — Checklist](#6-creating-a-new-module--checklist)
7. [File Upload Modules](#7-file-upload-modules)

---

## 1. Module Architecture Overview

Every module is composed of six layers, each with a single clear responsibility:

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
│  Calls $this->authorize() → delegates to Policy        │
│  Calls Model methods, returns View or JsonResponse      │
└────────────────────────┬────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────┐
│  POLICY  (app/Policies/)                                │
│  Fine gate — "Can THIS user act on THIS record?"        │
│  Wraps Spatie permission checks + row-level logic       │
│  Super Admin bypasses via before() hook                 │
└────────────────────────┬────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────┐
│  MODEL + OBSERVER  (app/Models/ + app/Observers/)       │
│  Model holds business rules, scopes, relationships      │
│  Observer fires side effects on every lifecycle event   │
│  HasActivityLog trait writes audit trail automatically  │
└─────────────────────────────────────────────────────────┘
```

---

## 2. Request & Permission Flow

### Step-by-step for a typical `update` action

| Step | Layer | What happens |
|------|-------|--------------|
| 1 | Route middleware | Checks `permission:view any {module}` — aborts 403 if user's role lacks it |
| 2 | Controller `edit()` | Calls `$this->authorize('update', $record)` |
| 3 | Policy `before()` | If user is Super Admin → returns `true`, skips everything else |
| 4 | Policy `update()` | Checks `$user->can('update {module}')` via Spatie + any row-level logic |
| 5 | Form Request | `authorize()` returns `true`; `rules()` validates the submitted data |
| 6 | Controller `update()` | Calls `$this->authorize('update', $record)` again (POST re-check), then saves |
| 7 | Model Observer `updating()` | Fires before the DB write |
| 8 | Model Observer `updated()` | Fires after the DB write — cascade events run here |
| 9 | HasActivityLog trait | Records the change to `activity_log` automatically |

### Why the controller re-checks on submit

The `edit()` and `update()` methods both call `$this->authorize('update', $record)`.
This prevents a user from bypassing the form by sending a direct `PUT` request even if they somehow obtained the URL.
Same pattern applies to `create()` / `store()`.

### Two types of authorization checks

| Type | Where | Example |
|------|-------|---------|
| **Role-based** | Route middleware | "Managers can view any employee" |
| **Row-level** | Policy method | "An employee can edit only their own profile" |

Row-level logic lives exclusively in the Policy — never in the controller or middleware.
See `EmployeePolicy::view()` and `EmployeePolicy::update()` for a working example.

---

## 3. File Structure Per Module

Using `BlogPost` as an example name:

```
app/
  Models/
    BlogPost.php                      — Fillable, casts, scopes, relationships, booted()
  Policies/
    BlogPostPolicy.php                — before(), viewAny(), view(), create(), update(), delete()
  Observers/
    BlogPostObserver.php              — created(), updated(), deleting(), deleted()
  Http/
    Controllers/
      BlogPostController.php          — CRUD methods, authorize() calls, DataTables AJAX
    Requests/
      StoreBlogPostRequest.php        — rules() + messages() for create
      UpdateBlogPostRequest.php       — rules() + messages() for update (ignore-self unique)

database/
  migrations/
    TIMESTAMP_create_blog_posts_table.php
  factories/
    BlogPostFactory.php
  seeders/
    BlogPostSeeder.php

resources/views/blog-posts/
  index.blade.php                     — DataTable + delete modal + JS
  create.blade.php                    — Create form
  edit.blade.php                      — Pre-filled form + danger zone
  show.blade.php                      — Read-only detail view

tests/Feature/
  BlogPostTest.php                    — CRUD tests + permission denial tests

routes/
  web.php                             — Permission-protected route group added here
```

---

## 4. Layer Responsibilities

### Model (`app/Models/`)

- Defines `$fillable`, `casts()`, relationships, query scopes
- Registers the Observer in `booted()` — this is the only place
- Uses `HasActivityLog` trait for automatic audit logging
- Uses `HasFactory` trait for test factories
- Does **not** contain authorization or HTTP logic

### Policy (`app/Policies/`)

- One Policy per Model, auto-discovered by Laravel via naming convention
- `before(User $user, string $ability): ?bool` — grants Super Admin full bypass
- One method per action: `viewAny`, `view`, `create`, `update`, `delete`
- Each method calls `$user->can('{permission name}')` via Spatie
- Row-level conditions (e.g., "own record only") go in `view()` and `update()`
- Returns `bool` only — no side effects, no DB writes

### Observer (`app/Observers/`)

- Registered in the Model's `booted()` method, not in a service provider
- Six lifecycle hooks: `creating`, `created`, `updating`, `updated`, `deleting`, `deleted`
- `deleting()` is the correct place for **cascade cleanup** — the record still exists
- `created()` / `updated()` are for **side effects** — notifications, sync, cache busting
- Loops through related records individually (not mass updates) so `HasActivityLog` captures each change
- Does **not** contain HTTP logic or redirect/response code

### Controller (`app/Http/Controllers/`)

- Calls `$this->authorize('{ability}', ModelClass::class)` for collection actions (`viewAny`, `create`)
- Calls `$this->authorize('{ability}', $record)` for record actions (`view`, `update`, `delete`)
- Both the form-display method and the form-submit method call `authorize()`
- Returns `View` for page renders, `JsonResponse` for AJAX/DataTables, `RedirectResponse` after writes
- Contains no business logic — delegates to Model, Service, or Observer

### Form Request (`app/Http/Requests/`)

- `authorize()` always returns `true` — authorization is handled by the Policy, not the Request
- `rules()` contains all validation rules as arrays of strings
- `UpdateRequest` uses `unique:table,column,{$id}` to exclude the record being edited
- `messages()` provides human-readable error messages per field

### Routes (`routes/web.php`)

Standard permission-protected group pattern:

```php
Route::prefix('{route-prefix}')
    ->name('{route-prefix}.')
    ->middleware('permission:view any {permission name}')
    ->group(function () {
        Route::get('/', [Controller::class, 'index'])->name('index');
        Route::get('/create', [Controller::class, 'create'])->name('create')->middleware('permission:create {permission name}');
        Route::post('/', [Controller::class, 'store'])->name('store')->middleware('permission:create {permission name}');
        Route::get('/{model}', [Controller::class, 'show'])->name('show');
        Route::get('/{model}/edit', [Controller::class, 'edit'])->name('edit')->middleware('permission:update {permission name}');
        Route::put('/{model}', [Controller::class, 'update'])->name('update')->middleware('permission:update {permission name}');
        Route::delete('/{model}', [Controller::class, 'destroy'])->name('destroy')->middleware('permission:delete {permission name}');
    });
```

---

## 5. Permission Naming Convention

| Action | Permission string | Middleware alias |
|--------|-------------------|-----------------|
| List | `view any {module}` | `permission:view any {module}` |
| Create | `create {module}` | `permission:create {module}` |
| Update | `update {module}` | `permission:update {module}` |
| Delete | `delete {module}` | `permission:delete {module}` |

The `{module}` segment is the **lowercase, space-separated plural** of the model name:

| Model | Module string | Route prefix |
|-------|---------------|--------------|
| `Department` | `departments` | `departments` |
| `LeaveType` | `leave types` | `leave-types` |
| `BlogPost` | `blog posts` | `blog-posts` |

Note: the permission string uses spaces (`blog posts`), the route prefix uses hyphens (`blog-posts`).

Permission records must exist in the database. Add new permissions to `database/seeders/RbacSeeder.php`.

---

## 6. Creating a New Module — Checklist

Follow this order to avoid dependency issues:

- [ ] **Migration** — define columns, run `php artisan migrate`
- [ ] **Model** — fillable, casts, scopes, `booted()` registering the Observer
- [ ] **Observer** — all six lifecycle hooks with cascade logic
- [ ] **Policy** — `before()` + five action methods using Spatie `can()`
- [ ] **Factory** — realistic fake data for tests and seeders
- [ ] **Seeder** — uses Factory, add to `DatabaseSeeder` if needed
- [ ] **StoreRequest** — validation rules + messages
- [ ] **UpdateRequest** — same rules with unique-ignore-self
- [ ] **Controller** — CRUD methods, `authorize()` per method, DataTables for `index()`
- [ ] **Views** — `index`, `create`, `edit`, `show` following existing module layout
- [ ] **Routes** — permission-protected group added to `routes/web.php`
- [ ] **Permissions** — add four permission records to `RbacSeeder`
- [ ] **Test** — feature test covering CRUD success paths and 403 denial cases

---

## 7. File Upload Modules

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
