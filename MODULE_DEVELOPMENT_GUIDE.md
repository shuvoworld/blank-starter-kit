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
6. [Reusable Form Components](#6-reusable-form-components)
7. [Permission Naming Convention](#7-permission-naming-convention)
8. [Creating a New Module — Checklist](#8-creating-a-new-module--checklist)
9. [File Upload Modules](#9-file-upload-modules)

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
│  destroy. Overrides authorizeAction() to call Policy.   │
│  Paired with a dedicated *DataTableController.          │
└────────────────────────┬────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────┐
│  POLICY  (app/Policies/)                                │
│  Fine gate — "Can THIS user perform THIS action?"       │
│  before() grants Superuser full bypass.                 │
│  Each method delegates to Spatie can() checks.          │
└────────────────────────┬────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────┐
│  MODEL + OBSERVER  (app/Models/ + app/Observers/)       │
│  Model: fillable, casts(), scopes, relationships        │
│  Model booted() registers the Observer                  │
│  Observer: cascade side-effects on lifecycle events     │
│  HasActivityLog trait: audit trail written automatically│
└─────────────────────────────────────────────────────────┘
```

---

## 2. Request & Permission Flow

### Step-by-step for a typical `update` action

| Step | Layer | What happens |
|------|-------|--------------|
| 1 | Route middleware | Checks `permission:update {module}` — aborts 403 if the user's role lacks it |
| 2 | BaseController `edit()` | Calls `$this->authorizeAction('update', $record)` |
| 3 | Controller `authorizeAction()` | Calls `$this->authorize('update', $record)` — dispatches to Policy |
| 4 | Policy `before()` | Superuser → returns `true`, skips everything else |
| 5 | Policy `update()` | Calls `$user->can('update {module}')` via Spatie |
| 6 | Form Request `authorize()` | Returns `true` — authorization is already handled |
| 7 | Form Request `rules()` | Validates the submitted data |
| 8 | Controller `update()` | Calls `$this->authorize('update', $record)` (POST re-check), then saves |
| 9 | Observer `updated()` | Fires after the DB write — side effects run here |
| 10 | HasActivityLog trait | Automatically records the change to the activity log |

### Two types of authorization checks

| Type | Where | Example |
|------|-------|---------|
| **Role-based** | Route middleware + Policy Spatie check | `permission:update leave types` on the PUT route |
| **Row-level** | Policy method logic | `EmployeePolicy::update()` — own record only |

Route middleware is the coarse gate ("does this role have access at all?"). The Policy is the fine gate ("can this specific user act on this specific record?"). Both must pass.

### Why the controller re-checks on submit

Both `edit()` and `update()` call `$this->authorize()`. This prevents a user from bypassing
the form by sending a direct `PUT` request even if they obtained the URL directly. The same
pattern applies to `create()` / `store()`.

---

## 3. File Structure Per Module

Using `LeaveType` as the reference example:

```
app/
  Models/
    LeaveType.php                        — Fillable, casts(), scopes, relationships
                                           booted() registers LeaveTypeObserver
                                           Traits: HasFactory, HasActivityLog
  Policies/
    LeaveTypePolicy.php                  — before() Superuser bypass
                                           viewAny(), view(), create(), update(), delete()
  Observers/
    LeaveTypeObserver.php                — deleting(): cascade cancel/delete dependents
                                           created(), updated(), deleted(): side effects
  Http/
    Controllers/
      LeaveTypeController.php            — Extends BaseController
                                           authorizeAction() → delegates to Policy
                                           Overrides: show(), store(), update()
                                           Inherits: index(), create(), edit(), destroy()
      LeaveTypeDataTableController.php   — Extends BaseDataTableController
                                           Defines: indexQuery(), dataTableColumns(),
                                           tableColumns(), actionColumn()
    Requests/
      StoreLeaveTypeRequest.php          — rules() + messages() for create
      UpdateLeaveTypeRequest.php         — same rules, unique-ignore-self via {record}

database/
  migrations/
    TIMESTAMP_create_leave_types_table.php
  seeders/
    LeaveTypeSeeder.php                  — firstOrCreate() for idempotent seeding

resources/views/leave-types/
  index.blade.php                        — DataTable listing, Create button (@can gated)
  form.blade.php                         — Shared create / edit form ($editing boolean)
  show.blade.php                         — Read-only detail view (Edit/Delete @can gated)

tests/Feature/
  LeaveTypeTest.php                      — CRUD success paths + 403 denial cases

routes/
  web.php                                — Permission-protected route group
```

> **Factory** is optional — add one when the module has tests that need model instances
> or a seeder that benefits from realistic fake data.

---

## 4. Layer Responsibilities

### Model (`app/Models/`)

- Defines `$fillable`, `casts()` method, relationships, and query scopes
- Registers the Observer in `booted()` — this is the only place observers are registered
- Uses `HasActivityLog` trait — all create/update/delete events are logged automatically
- Uses `HasFactory` trait when a factory is required
- Does **not** contain authorization or HTTP logic

```php
protected static function booted(): void
{
    static::observe(LeaveTypeObserver::class);
}
```

### Policy (`app/Policies/`)

- One Policy per Model, auto-discovered by Laravel via naming convention (`LeaveType` → `LeaveTypePolicy`)
- `before(User $user, string $ability): ?bool` — returns `true` for Superuser, `null` otherwise to continue
- Five action methods: `viewAny`, `view`, `create`, `update`, `delete`
- Each method calls `$user->can('{permission string}')` via Spatie — no direct DB logic
- Row-level conditions (e.g. "own record only") go directly inside `view()` / `update()` — see `EmployeePolicy` for an example
- Returns `bool` only — no side effects, no DB writes

```php
public function before(User $user, string $ability): ?bool
{
    if ($user->hasRole('Superuser')) {
        return true;
    }
    return null;
}

public function update(User $user, LeaveType $leaveType): bool
{
    return $user->can('update leave types');
}
```

### Observer (`app/Observers/`)

- Registered in the Model's `booted()` method — never in a service provider
- `deleting()` — fires **before** deletion; correct place for cascade cleanup (record still exists)
- `deleted()` — fires **after** deletion; use for cache busting, external cleanup
- `created()` / `updated()` — use for side effects (notifications, sync, etc.)
- Loop through related records individually (not mass updates) so `HasActivityLog` captures each change

```php
public function deleting(LeaveType $leaveType): void
{
    // Cancel pending requests — loop individually for activity log
    LeaveRequest::query()
        ->where('leave_type_id', $leaveType->id)
        ->where('status', 'pending')
        ->get()
        ->each(function ($req) {
            $req->status = 'cancelled';
            $req->save();
        });

    // Balances are meaningless without the leave type — delete them
    LeaveBalance::query()->where('leave_type_id', $leaveType->id)->delete();
}
```

### Controller (`app/Http/Controllers/`)

Extends `BaseController`. Override `authorizeAction()` to wire in the Policy, then override
only the methods that need module-specific logic:

```php
protected function authorizeAction(string $ability, ?Model $record = null): void
{
    $record
        ? $this->authorize($ability, $record)
        : $this->authorize($ability, LeaveType::class);
}
```

| Scenario | Override |
|----------|----------|
| Policy authorization needed | Override `authorizeAction()` |
| `show()` needs a dedicated view | Override `show()` |
| `store()` / `update()` save data | Override both; call `$this->authorize()` explicitly |
| Create form needs extra view data | Override `createViewData()` |
| Edit form needs extra view data | Override `editViewData()` |
| Record must be guarded before deletion | Override `beforeDestroy()` |
| Post-delete cleanup needed | Override `afterDestroy()` |

Returns `View` for page renders, `JsonResponse` for AJAX, `RedirectResponse` after writes.

### DataTable Controller (`app/Http/Controllers/`)

Extends `BaseDataTableController`. Handles the AJAX-only `/{module}/datatable` endpoint.

| Method | Purpose |
|--------|---------|
| `indexQuery()` | Base Eloquent query (ordering, eager loads, scopes) |
| `dataTableColumns()` | Map of `column_key => fn($record) => string` for computed/HTML columns |
| `tableColumns()` | Column definitions sent to the frontend (header labels, widths, orderable flags) |
| `actionColumn()` | Override to add a View button alongside the default Edit / Delete |

Always declare `$rawColumns` for any column key that outputs HTML.

### Form Request (`app/Http/Requests/`)

- `authorize()` always returns `true` — Policy handles authorization
- `rules()` contains all validation as arrays of rule strings
- `UpdateRequest` reads `$this->route('record')` to exclude the current record from unique checks
- `messages()` provides human-readable errors per field

### Views (`resources/views/{module}/`)

| File | Purpose |
|------|---------|
| `index.blade.php` | Renders the DataTable. Passes `tableColumns` (PHP, for `<thead>`) and `dtColumns` (JSON, for JS config). Create button wrapped in `@can('create', Model::class)` |
| `form.blade.php` | Shared for create and edit. Uses `$editing` boolean and `$record` (null on create) |
| `show.blade.php` | Read-only detail. Edit/Delete buttons gated with `@can('update', $record)` / `@can('delete', $record)` |

Use `@include('layouts.form.inputs.*')` partials for all form fields.
For Select2-enhanced selects, pass `'select2' => true` — the partial renders `data-toggle="select2"`.

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

Both classes live in `app/Http/Controllers/BaseController/` and are `abstract` — they are never
instantiated directly. Every module controller extends one of them.

---

### `BaseController`

**File:** `app/Http/Controllers/BaseController/BaseController.php`

Provides all standard CRUD actions. A child controller only needs to implement `store()`,
`update()`, and any overrides that differ from the default behaviour.

#### Properties

Set these in the child controller's `__construct()` — they drive all inherited methods.

| Property | Type | Purpose |
|----------|------|---------|
| `$model` | `string` | Fully-qualified model class, e.g. `LeaveType::class` |
| `$routePrefix` | `string` | Named-route prefix, e.g. `'leave-types'` |
| `$viewPrefix` | `string` | Blade view folder, e.g. `'leave-types'` |
| `$resourceName` | `string` | Human label used in flash messages, e.g. `'Leave type'` |
| `$dataTableController` | `?BaseDataTableController` | Injected DataTable controller, or `null` if not used |

#### Internal helpers

**`findRecord(int|string $id): Model`**
Calls `Model::findOrFail($id)` using `$this->model`. Throws a 404 if the record does not exist.
Used by `edit()`, `show()`, `destroy()`, and child `update()` methods.

**`resolveMessage(string $action): string`** *(private)*
Returns the flash message for a given action key (`'created'`, `'updated'`, `'deleted'`).
Merges the child's `messages()` override into a set of defaults built from `$resourceName`:
```
'created' => 'Leave type created successfully.'
'updated' => 'Leave type updated successfully.'
'deleted' => 'Leave type deleted successfully.'
```

**`messages(): array`** *(override to customise)*
Return only the keys that differ. Merged over the defaults by `resolveMessage()`.
```php
protected function messages(): array
{
    return ['deleted' => 'This leave type has been permanently removed.'];
}
```

**`successRedirect(string $action): RedirectResponse`**
Redirects to `{routePrefix}.index` and flashes `status` with the resolved message.
Called at the end of `store()`, `update()`, and `destroy()`.

#### Authorization hook

**`authorizeAction(string $ability, ?Model $record = null): void`** *(override to wire Policy)*

No-op by default. Called by `index()`, `create()`, `edit()`, and `destroy()` before any action.
Override in the child controller to delegate to a Policy:

```php
protected function authorizeAction(string $ability, ?Model $record = null): void
{
    $record
        ? $this->authorize($ability, $record)
        : $this->authorize($ability, LeaveType::class);
}
```

The `ability` string passed by the base methods maps directly to Policy method names:

| Caller | Ability passed |
|--------|---------------|
| `index()` | `'viewAny'` |
| `create()` | `'create'` |
| `edit()` | `'update'` |
| `destroy()` | `'delete'` |

#### CRUD methods

**`index(Request $request): View`**
1. Calls `authorizeAction('viewAny')`
2. Fetches `tableColumns` from the DataTable controller (full PHP definitions including labels and widths — used to build `<thead>`)
3. Derives `dtColumns` from `tableColumns` keeping only what the DataTables JS library needs (`data`, `name`, `orderable`, `searchable`, `className`)
4. Renders `{viewPrefix}.index` with both variables

**`create(): View`**
1. Calls `authorizeAction('create')`
2. Renders `{viewPrefix}.form` with `editing = false`, `record = null`, plus anything returned by `createViewData()`

**`createViewData(): array`** *(override to inject extra data on create)*
Return an associative array merged into the create form view. Use this to pass dropdown option lists, default values, etc.
```php
protected function createViewData(): array
{
    return ['departments' => Department::active()->pluck('name', 'id')];
}
```

**`show(int|string $record): View|RedirectResponse`**
Default implementation redirects to the `edit` route. Override when a module has a dedicated read-only detail view:
```php
public function show(int|string $record): View
{
    $leaveType = $this->findRecord($record);
    $this->authorize('view', $leaveType);

    return view('leave-types.show', compact('leaveType'));
}
```

**`edit(int|string $record): View`**
1. Calls `findRecord($record)`
2. Calls `authorizeAction('update', $model)`
3. Renders `{viewPrefix}.form` with `editing = true`, `record = $model`, plus anything returned by `editViewData($model)`

**`editViewData(Model $record): array`** *(override to inject extra data on edit)*
Same as `createViewData()` but receives the existing model. `$record` is already passed to the view automatically; only return _additional_ data.

**`destroy(int|string $record): JsonResponse|RedirectResponse`**
1. Calls `findRecord($record)`
2. Calls `authorizeAction('delete', $model)`
3. Calls `beforeDestroy($model)` — abort here to cancel
4. Calls `$model->delete()` — triggers Observer `deleting()` / `deleted()`
5. Calls `afterDestroy($model)`
6. If AJAX: returns `JsonResponse(['message' => '...'])`
7. Otherwise: returns `successRedirect('deleted')`

**`beforeDestroy(Model $record): void`** *(override to guard deletion)*
Called before `delete()`. Throw an exception or call `abort()` here to prevent deletion.
```php
protected function beforeDestroy(Model $record): void
{
    abort_if($record->is_system, 403, 'System records cannot be deleted.');
}
```

**`afterDestroy(Model $record): void`** *(override for post-delete side effects)*
Called after `delete()`. Use for cache clearing, event firing, or any cleanup that should happen
in the HTTP layer rather than the Observer.

---

### `BaseDataTableController`

**File:** `app/Http/Controllers/BaseController/BaseDataTableController.php`

Handles the AJAX-only `/{module}/datatable` endpoint consumed by the DataTables JS library on
the index page. It is never called by a browser directly.

#### Properties

Set these in the child's `__construct()`.

| Property | Type | Purpose |
|----------|------|---------|
| `$model` | `string` | Fully-qualified model class |
| `$routePrefix` | `string` | Named-route prefix — used to build action button URLs |
| `$withRelations` | `array` | Relations eager-loaded on every `indexQuery()` call |
| `$rawColumns` | `array` | Column keys whose values contain raw HTML (child-defined) |

`$baseRawColumns` is a private constant `['action']` that is always merged with `$rawColumns`
so the action column is never accidentally escaped. Never declare `'action'` in your own
`$rawColumns` — it is always included.

#### The `datatable()` pipeline

The single public method `datatable(Request $request): JsonResponse` orchestrates the full
DataTables response. Its steps in order:

```
1. abort_unless($request->ajax(), 403)     — rejects non-AJAX requests
2. authorizeDataTable()                    — optional auth hook (no-op by default)
3. DataTables::of(indexQuery())            — builds the Eloquent-backed DT instance
   ->addIndexColumn()                      — adds DT_RowIndex (sequential row number)
4. foreach dataTableColumns()              — registers each custom/computed column via addColumn()
5. addColumn('action', actionColumn())     — appends the action buttons column
6. applyFilters($dt, $request)             — optional filter hook (no-op by default)
7. rawColumns([...baseRaw, ...childRaw])   — marks HTML columns safe from auto-escaping
8. $dt->make(true)                         — returns the JSON response
```

#### Methods

**`authorizeDataTable(): void`** *(override to gate the endpoint)*
No-op by default. Override to add a `$this->authorize()` check if the DataTable endpoint
needs protection beyond route middleware.

**`indexQuery(): Builder`** *(override to customise the base query)*
Default: `Model::query()->with($this->withRelations)`.
Override to add default ordering, scopes, or filters that always apply:
```php
protected function indexQuery(): Builder
{
    return LeaveType::query()->orderBy('sort_order');
}
```

**`dataTableColumns(): array`** *(override to add computed/HTML columns)*
Returns a map of `'column_key' => callable`. Each callable receives the current model instance
and must return a string. Declare every key that returns HTML in `$rawColumns`.
```php
protected function dataTableColumns(): array
{
    return [
        'status_badge' => fn (LeaveType $lt) =>
            '<span class="badge ' . ($lt->is_active ? 'bg-success' : 'bg-secondary') . '">'
            . ($lt->is_active ? 'Active' : 'Inactive') . '</span>',
    ];
}
```

**`tableColumns(): array`** *(override to define the full column set)*
Returns the column definition array used in two places:
- **PHP side** (`tableColumns`): passed to `index.blade.php` to build `<thead>` labels and widths
- **JS side** (`dtColumns`): stripped down to `data/name/orderable/searchable/className` by `BaseController::index()` and JSON-encoded for the DataTables initialiser

Each column definition supports these keys:

| Key | Required | Purpose |
|-----|----------|---------|
| `data` | Yes | DataTables column data source (matches JSON key or custom column key) |
| `name` | Yes | Server-side column name for ordering/searching |
| `label` | Yes | `<th>` header text |
| `width` | No | Fixed column width in pixels |
| `orderable` | No | Whether the column is sortable (default `true`) |
| `searchable` | No | Whether the column is included in global search (default `true`) |
| `className` | No | CSS classes applied to every cell in this column |

Default columns provided by the base implementation: `#` (DT_RowIndex), `name`, `is_active`, `action`.

**`actionColumn($record): string`** *(override to add extra action buttons)*
Default renders Edit (primary) + Delete (danger/AJAX) buttons in a `btn-group-sm`.
Override when a module needs a View button or other actions:
```php
protected function actionColumn($leaveType): string
{
    $showUrl   = route('leave-types.show', $leaveType);
    $editUrl   = route('leave-types.edit', $leaveType);
    $deleteUrl = route('leave-types.destroy', $leaveType);

    return '
        <div class="btn-group btn-group-sm">
            <a href="' . $showUrl . '" class="btn btn-info" title="View">
                <i class="bi bi-eye"></i>
            </a>
            <a href="' . $editUrl . '" class="btn btn-primary" title="Edit">
                <i class="bi bi-pencil"></i>
            </a>
            <button type="button" class="btn btn-danger btn-delete"
                data-url="' . $deleteUrl . '"
                data-name="' . e($leaveType->name) . '" title="Delete">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    ';
}
```

**`getRecordName($record): string`**
Used by the default `actionColumn()` to populate `data-name` on the delete button (shown in the
confirmation prompt). Tries `$record->name`, then `$record->title`, then `(string) $record->id`.
Override if the model uses a different display field.

**`applyFilters(EloquentDataTable $dt, Request $request): EloquentDataTable`** *(override for column filters)*
No-op by default. Receives the DataTables instance and the HTTP request. Use `$dt->filterColumn()`
or manual `$dt->whereHas()` calls here to apply status dropdowns, date ranges, etc.

#### Constructor wiring

```php
// In ModuleController:
public function __construct(ModuleDataTableController $dataTableController)
{
    $this->model               = Module::class;
    $this->routePrefix         = 'modules';
    $this->viewPrefix          = 'modules';
    $this->resourceName        = 'Module';
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

## 6. Reusable Form Components

All form fields in module views use a shared component system. Every component is a PHP View
Component class extending `BaseInput`, paired with a Blade template. You never instantiate the
classes directly — call them via an `@include` partial.

---

### Usage pattern

```blade
@include('layouts.form.inputs.{type}', ['var' => [
    'name'  => 'field_name',
    'label' => 'Field Label',
    'value' => $record?->field_name,
    ...options...
]])
```

Every component reads its config from the `var` array. The partial resolves to
`resources/views/layouts/form/inputs/{type}.blade.php`, which is a thin wrapper that
`@include`s the actual component template at `resources/views/layouts/form/components/`.

---

### `BaseInput` — common params

All input types share these keys (from `BaseInput::__construct()`):

| Key | Default | Purpose |
|-----|---------|---------|
| `name` | _(required)_ | HTML `name` attribute and old-input lookup key |
| `label` | `null` | `<label>` text; omit to suppress the label |
| `value` / `val` | `null` | Pre-populated value; overridden by `old()` after failed validation |
| `div` / `container_class` | `'col-md-3'` | Outer column class (`col-md-*`, `col-lg-*`, etc.) |
| `id` | auto-derived from `name` | Explicit HTML `id`; auto-generated if omitted |
| `class` | `''` | Extra CSS classes appended to the input element |
| `label_class` | `null` | Extra CSS classes on the `<label>` |
| `placeholder` | `''` | `placeholder` attribute |
| `required` | `false` | Adds `required` attribute and red asterisk to label |
| `disabled` | `false` | Adds `disabled` attribute |
| `readonly` | `false` | Adds `readonly` attribute |
| `autofocus` | `false` | Adds `autofocus` attribute |
| `tooltip` | `null` | Renders a help icon with Bootstrap tooltip |
| `params` | `[]` | Arbitrary extra HTML attributes; see `extraHtml()` below |

#### Old-input repopulation

`resolvedValue()` is called by every template to determine the displayed value:

1. If `old()` session data is present (failed validation redirect) — use the submitted value
2. Otherwise — use the passed `value` / `val`
3. Fallback — empty string

This means you never need to write `old('field', $record->field)` in views manually — the
component handles it automatically.

#### `params` — extra HTML attributes

Pass any arbitrary HTML attribute via the `params` key. Values are escaped by `extraHtml()` to
prevent XSS:

```blade
@include('layouts.form.inputs.text', ['var' => [
    'name'   => 'sort_order',
    'label'  => 'Sort Order',
    'type'   => 'number',
    'value'  => $record?->sort_order ?? 0,
    'params' => ['min' => '0', 'max' => '999', 'step' => '1'],
]])
```

Renders: `<input ... min="0" max="999" step="1">`

---

### Input types

#### `text` — Text / number / email / date inputs

```blade
@include('layouts.form.inputs.text', ['var' => [
    'name'        => 'name',
    'label'       => 'Name',
    'value'       => $record?->name,
    'placeholder' => 'e.g., Sick Leave',
    'div'         => 'col-md-6',
    'required'    => true,
    'autofocus'   => true,
]])
```

Change the input type with `'type' => 'number'` / `'email'` / `'date'` etc. (default `'text'`).

#### `textarea` — Multi-line text

```blade
@include('layouts.form.inputs.textarea', ['var' => [
    'name'        => 'description',
    'label'       => 'Description',
    'value'       => $record?->description,
    'placeholder' => 'Leave type description',
    'rows'        => 3,
    'div'         => 'col-md-12',
]])
```

Extra key: `rows` (default `4`).

#### `select` — Static options dropdown

Pass a PHP `['value' => 'Label']` array. The component's `isSelected()` method handles
old-input awareness automatically (including array/multi-select comparisons via string casting).

```blade
@include('layouts.form.inputs.select', ['var' => [
    'name'    => 'is_paid',
    'label'   => 'Is Paid',
    'value'   => $record?->is_paid ?? 1,
    'options' => [1 => 'Yes', 0 => 'No'],
    'div'     => 'col-md-4',
    'required' => true,
    'select2' => true,
]])
```

Extra keys:

| Key | Default | Purpose |
|-----|---------|---------|
| `options` | `[]` | Associative array `['value' => 'Label']` |
| `prompt` | `null` | Placeholder option shown when no value is selected |
| `multiple` | `false` | Multi-select; use array `name="field[]"` |
| `select2` | `false` | Enhance with Select2 (renders `data-toggle="select2"`) |
| `allow_clear` | `false` | Show a Select2 clear (×) button; requires `prompt` to be set |

#### `select-model` — DB-driven dropdown

Queries an Eloquent model at render time. No need to pass options from the controller.

```blade
@include('layouts.form.inputs.select-model', ['var' => [
    'name'        => 'department_id',
    'label'       => 'Department',
    'value'       => $record?->department_id,
    'model'       => \App\Models\Department::class,
    'key_field'   => 'id',
    'label_field' => 'name',
    'scopes'      => ['active'],
    'order_by'    => ['name', 'asc'],
    'div'         => 'col-md-4',
    'required'    => true,
    'select2'     => true,
]])
```

Extra keys (all optional beyond `model`):

| Key | Default | Purpose |
|-----|---------|---------|
| `model` | _(required)_ | Fully-qualified model class |
| `key_field` | `'id'` | Model attribute used as `<option value>` |
| `label_field` | `'name'` | Model attribute used as option text |
| `conditions` | `[]` | `['column' => 'value']` pairs added as `where()` clauses |
| `scopes` | `[]` | Named scopes applied to the query — string `'active'` or array `['forYear', 2024]` |
| `order_by` | `[$label_field, 'asc']` | `['column', 'direction']` |
| `query` | `null` | `Closure` receiving the `Builder`; use for complex filtering |
| `prompt` | `null` | Placeholder option |
| `multiple` | `false` | Multi-select |
| `select2` | `false` | Select2 enhancement |
| `allow_clear` | `false` | Select2 clear button |

Closure query example:

```blade
'query' => fn ($q) => $q->whereNull('archived_at')->where('company_id', auth()->user()->company_id),
```

#### `switch` — Toggle switch

```blade
@include('layouts.form.inputs.switch', ['var' => [
    'name'  => 'is_active',
    'label' => 'Active',
    'value' => $record?->is_active ?? true,
    'div'   => 'col-md-3',
]])
```

Renders a Bootstrap-styled toggle switch. The underlying input is a checkbox. Pass `'value' => true`
to default it on, `false` for off. Old-input awareness is inherited from `BaseInput`.

#### `checkbox` — Checkbox group

```blade
@include('layouts.form.inputs.checkbox', ['var' => [
    'name'        => 'permissions[]',
    'label'       => 'Permissions',
    'options'     => ['read' => 'Read', 'write' => 'Write', 'delete' => 'Delete'],
    'value'       => $record?->permissions ?? [],
    'group_label' => 'Select permissions',
    'inline'      => true,
    'div'         => 'col-md-12',
]])
```

Extra keys:

| Key | Default | Purpose |
|-----|---------|---------|
| `options` | `[]` | `['value' => 'Label']` array of checkboxes to render |
| `group_label` | `null` | Secondary label shown above the checkbox group |
| `inline` | `false` | Render checkboxes side-by-side instead of stacked |

#### `radio` — Radio button group

```blade
@include('layouts.form.inputs.radio', ['var' => [
    'name'    => 'applicable_gender',
    'label'   => 'Gender',
    'options' => ['male' => 'Male', 'female' => 'Female'],
    'value'   => $record?->applicable_gender,
    'inline'  => true,
    'div'     => 'col-md-6',
]])
```

Same keys as `checkbox` (no `multiple` — radio groups are always single-select).

#### `file` — File upload

```blade
@include('layouts.form.inputs.file', ['var' => [
    'name'     => 'attachment',
    'label'    => 'Attachment',
    'required' => false,
    'div'      => 'col-md-6',
    'params'   => ['accept' => '.pdf,.doc,.docx'],
]])
```

Renders a Bootstrap `form-control` file input. Accepts any `params` attribute (e.g., `accept`,
`multiple`). For modules using Spatie MediaLibrary, add `enctype="multipart/form-data"` to the
`<form>` tag and handle uploads in the controller's `store()` / `update()` methods.

---

### Select2 integration

When `'select2' => true` is passed, the component renders `data-toggle="select2"` on the
`<select>` element. The global DOMContentLoaded listener in `resources/js/bootstrap.js`
picks up all `select[data-toggle="select2"]` elements and initialises them with the
`bootstrap-5` theme.

> **Important:** The attribute name is `data-toggle="select2"`, **not** `data-select2`. The
> key `select2` is used internally by Select2 on `element.dataset`; using it as an HTML
> attribute causes a `destroy is not a function` TypeError on re-initialisation.

Enable the clear button alongside a placeholder:

```blade
@include('layouts.form.inputs.select', ['var' => [
    'name'       => 'applicable_gender',
    'label'      => 'Applicable Gender',
    'value'      => $record?->applicable_gender,
    'prompt'     => 'Not Applicable',
    'options'    => ['male' => 'Male', 'female' => 'Female', 'other' => 'Other'],
    'select2'    => true,
    'allow_clear' => true,
    'div'        => 'col-md-4',
]])
```

`allow_clear` only works when `prompt` is also set (Select2 needs a blank option to clear to).

---

## 7. Permission Naming Convention

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

## 8. Creating a New Module — Checklist

Follow this order to avoid dependency issues:

- [ ] **Migration** — define columns, run `php artisan migrate`
- [ ] **Model** — `$fillable`, `casts()`, scopes, relationships; add `HasActivityLog` and `HasFactory` traits; add `booted()` to register the Observer
- [ ] **Observer** — `deleting()` for cascade cleanup; `created()` / `updated()` for side effects
- [ ] **Policy** — `before()` Superuser bypass + five action methods delegating to Spatie `can()`
- [ ] **Seeder** — use `firstOrCreate()` for idempotent seeding; add to `DatabaseSeeder` if needed
- [ ] **StoreRequest** — `rules()` + `messages()`
- [ ] **UpdateRequest** — same rules with `unique:table,col,$this->route('record')` ignore-self
- [ ] **DataTable Controller** — extend `BaseDataTableController`; implement `indexQuery()`, `dataTableColumns()`, `tableColumns()`, and override `actionColumn()` if a View button is needed
- [ ] **Controller** — extend `BaseController`; wire constructor; override `authorizeAction()` to call Policy; override `show()`, `store()`, `update()` with explicit `$this->authorize()` calls
- [ ] **Views** — `index.blade.php`, `form.blade.php` (shared, `$editing` flag), `show.blade.php`
- [ ] **Routes** — permission-protected group in `routes/web.php`; `/datatable` before `/{record}`
- [ ] **Permissions** — add four permission records to `RbacSeeder`
- [ ] **Test** — feature test covering create, update, delete success paths and 403 denial cases

---

## 9. File Upload Modules

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
