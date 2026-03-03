# Leave & Attendance Management System

## Overview

The Leave & Attendance Management System is a comprehensive Laravel-based module that enables organizations to manage employee leave requests, track leave balances, and maintain holiday calendars. The system supports configurable leave types, automated leave calculations, and role-based access control.

## Table of Contents

- [Features](#features)
- [Architecture](#architecture)
- [Leave Types Module — Implementation](#leave-types-module--implementation)
- [Database Schema](#database-schema)
- [User Roles & Permissions](#user-roles--permissions)
- [Leave Request Workflow](#leave-request-workflow)
- [Installation](#installation)
- [Usage Guide](#usage-guide)
- [Routes Reference](#routes-reference)
- [Testing](#testing)

---

## Features

### 1. Leave Type Management
- Create and manage multiple leave types (Annual, Sick, Casual, Maternity, Paternity, Unpaid)
- Configurable rules per leave type:
  - Maximum days per year/month
  - Paid/unpaid leave designation
  - Approval requirement
  - Document requirement
  - Carry-forward limits
  - Pro-rata calculation for mid-year joiners
  - Gender-specific leave types
  - Minimum advance notice requirement

### 2. Holiday Calendar
- Global holidays applicable to all employees
- Regional holidays linked to countries/cities
- Recurring annual holidays
- Automatic exclusion from leave day calculations

### 3. Leave Request Workflow
- Employees can submit leave requests with date ranges
- Automatic calculation of actual leave days (excluding weekends and holidays)
- Real-time balance checking
- Approval/Rejection by authorized personnel
- Cancellation of pending requests
- Status tracking: Pending → Approved/Rejected/Cancelled

### 4. Leave Balance Management
- Automatic balance initialization for all active leave types
- Per-year balance tracking
- Separate tracking for:
  - Total entitlement
  - Taken days
  - Remaining days
  - Pending days
- Carry-forward from previous year
- Pro-rata calculation for new employees

### 5. Employee Dashboard
- Personal leave balance summary
- Pending leave requests
- Upcoming holidays (next 30 days)
- Quick access to leave-related actions

---

## Architecture

### Directory Structure

```
app/
├── Models/
│   ├── LeaveType.php                        # Leave type definition & rules
│   ├── LeaveRequest.php                     # Leave request with state
│   └── LeaveBalance.php                     # Per-user balance tracking
├── Http/
│   ├── Controllers/
│   │   ├── BaseController/
│   │   │   ├── BaseController.php           # Shared CRUD scaffold
│   │   │   └── BaseDataTableController.php  # Shared DataTable scaffold
│   │   ├── LeaveTypeController.php          # CRUD controller (extends BaseController)
│   │   ├── LeaveTypeDataTableController.php # DataTable AJAX controller
│   │   └── ...
│   └── Requests/
│       ├── StoreLeaveTypeRequest.php
│       ├── UpdateLeaveTypeRequest.php
│       └── ...

resources/views/
├── leave-types/
│   ├── index.blade.php    # DataTable listing
│   ├── form.blade.php     # Shared create / edit form
│   └── show.blade.php     # Detail / read-only view
└── ...

database/
├── migrations/
│   └── *_create_leave_types_table.php
└── seeders/
    └── LeaveTypeSeeder.php
```

### Design Patterns

1. **BaseController scaffold** — All CRUD modules extend `BaseController`, which provides `index`, `create`, `edit`, `destroy`, and a default `show` redirect. Child controllers only implement `store`, `update`, and any view overrides needed.
2. **Split DataTable controller** — Each module has a dedicated `*DataTableController` (extends `BaseDataTableController`) that handles the AJAX `/datatable` endpoint, keeping DataTable configuration out of the main controller.
3. **Form Requests** — All validation lives in dedicated `Store*Request` and `Update*Request` classes.
4. **Activity Logging** — The `HasActivityLog` trait is included on models to automatically record create/update/delete events.

---

## Leave Types Module — Implementation

This section documents exactly how the Leave Types module is built.

### Controllers

#### `LeaveTypeController`

Extends `BaseController`. The base class provides the majority of CRUD behaviour; this controller only adds what is specific to leave types.

| Method | Provided by | Notes |
|--------|-------------|-------|
| `index()` | `BaseController` | Passes `tableColumns` / `dtColumns` to the index view |
| `create()` | `BaseController` | Renders `leave-types.form` with `editing = false` |
| `show()` | **Override** | Renders `leave-types.show` with `$leaveType` variable |
| `edit()` | `BaseController` | Renders `leave-types.form` with `editing = true`, `record = $model` |
| `store()` | **Override** | Validates via `StoreLeaveTypeRequest`, calls `LeaveType::create()`, redirects with flash |
| `update()` | **Override** | Validates via `UpdateLeaveTypeRequest`, calls `$record->update()`, redirects with flash |
| `destroy()` | `BaseController` | Supports both AJAX (JSON) and standard redirect; fires `beforeDestroy` / `afterDestroy` hooks |

Constructor wires the base class properties:
```php
$this->model             = LeaveType::class;
$this->routePrefix       = 'leave-types';
$this->viewPrefix        = 'leave-types';
$this->resourceName      = 'Leave type';
$this->dataTableController = $dataTableController;
```

#### `LeaveTypeDataTableController`

Extends `BaseDataTableController`. Handles the `GET /leave-types/datatable` AJAX-only endpoint consumed by the DataTable on the index page.

**Custom columns rendered as HTML badges:**

| Column key | Source field | Output |
|---|---|---|
| `code_badge` | `code` | `<span class="badge bg-info">` |
| `is_paid_badge` | `is_paid` | Green "Paid" / Yellow "Unpaid" badge |
| `carry_forward_badge` | `carry_forward` | Green "Yes (max N)" / Grey "No" badge |
| `status_badge` | `is_active` | Green "Active" / Grey "Inactive" badge |

**`indexQuery()`** returns `LeaveType::query()->orderBy('sort_order')`, so records always appear in the configured display order.

**Action column** renders three buttons per row: View (info), Edit (primary), Delete (danger/AJAX).

**`tableColumns()`** defines the DataTables column definitions sent to the frontend:
`#`, `Name`, `Code`, `Paid`, `Status`, `Actions`.

### Model — `LeaveType`

Traits: `HasFactory`, `HasActivityLog`

**Fillable fields (18 total):**

| Field | Type | Default | Purpose |
|-------|------|---------|---------|
| `name` | string | — | Display name |
| `code` | string | — | Unique short code (e.g. AL, SL) |
| `description` | text nullable | — | Free text description |
| `is_paid` | boolean | `true` | Whether leave is compensated |
| `requires_approval` | boolean | `true` | Whether manager sign-off is needed |
| `max_days_per_year` | int nullable | — | Annual cap |
| `max_days_per_month` | int nullable | — | Monthly cap |
| `carry_forward` | boolean | `false` | Allow unused days to roll over |
| `carry_forward_limit` | int nullable | — | Max days that can roll over |
| `carry_forward_expiry_days` | int nullable | — | Days before rolled-over leave expires |
| `requires_document` | boolean | `false` | Mandatory supporting document |
| `min_days_before_request` | int nullable | — | Minimum advance notice |
| `max_consecutive_days` | int nullable | — | Consecutive day cap |
| `is_gender_specific` | boolean | `false` | Restrict by gender |
| `applicable_gender` | enum nullable | — | `male` / `female` / `other` |
| `is_paid_pro_rata` | boolean | `false` | Pro-rata for mid-year joiners |
| `is_active` | boolean | `true` | Soft enable/disable |
| `sort_order` | int | `0` | DataTable display order |

**Casts** (via `casts()` method): all boolean fields cast to `bool`.

**Scopes:**
- `scopeActive()` — filters `is_active = true`.

**Relationships:**
- `leaveRequests()` — `hasMany(LeaveRequest::class)`
- `leaveBalances()` — `hasMany(LeaveBalance::class)`

### Form Requests

Both requests share the same field rules. The key difference is uniqueness:

- `StoreLeaveTypeRequest` — `unique:leave_types,name` and `unique:leave_types,code` (global)
- `UpdateLeaveTypeRequest` — ignores the current record's ID: `unique:leave_types,name,{$id}` and `unique:leave_types,code,{$id}`

The route parameter is `{record}`, retrieved via `$this->route('record')` in the update request.

Custom error messages are defined for `name.required`, `name.unique`, `code.required`, `code.unique`, and `applicable_gender.in`.

### Views

All views extend `layouts.form.app` and use the project's shared form component partials.

#### `index.blade.php`
- Renders an AdminLTE card with a DataTable powered by the `LeaveTypeDataTableController`.
- A "Create" button is shown only to users with the `create leave types` permission (`@can`).
- Passes `tableColumns` (PHP column definitions for `<thead>`) and `dtColumns` (JSON for DataTables JS config) from the controller.

#### `form.blade.php` (shared create / edit)
- Controlled by the `$editing` boolean passed from the controller.
- Uses `@include('layouts.form.inputs.*')` partials for all fields.
- Select fields that use Select2 pass `'select2' => true`; the rendered `<select>` gets `data-toggle="select2"` which the global JS initialiser picks up.
- When `$editing` is true, renders a metadata card (ID, Created At, Updated At) and — with the `delete leave types` permission — a Danger Zone delete form.
- Pushes an inline script `@push('scripts')` to enforce uppercase on the `code` input.

#### `show.blade.php`
- Read-only detail view of a single leave type.
- Displays all 18 fields grouped into logical sections (Basic Info, Rules, Carry Forward, etc.).
- Edit and Delete action buttons appear with the appropriate `@can` checks.

### Seeder — `LeaveTypeSeeder`

Seeds 6 default leave types using `LeaveType::firstOrCreate(['code' => ...], $data)` so re-running is safe:

| Code | Name | Paid | Max/Year | Carry Forward | Gender |
|------|------|------|----------|---------------|--------|
| AL | Annual Leave | Yes | 20 | Yes (max 5, 90-day expiry) | Any |
| SL | Sick Leave | Yes | 10 | No | Any |
| CL | Casual Leave | Yes | 12 | No | Any |
| ML | Maternity Leave | Yes | 90 | No | Female |
| PL | Paternity Leave | Yes | 14 | No | Male |
| UL | Unpaid Leave | No | Unlimited | No | Any |

### Routes

Defined in `routes/web.php` under the `leave-types` prefix and `permission:view any leave types` middleware group. The route parameter name is `{record}` (matched by the base controller's `findRecord()` helper).

| Method | URI | Route name | Middleware |
|--------|-----|------------|------------|
| GET | `/leave-types/datatable` | `leave-types.datatable` | `view any leave types` |
| GET | `/leave-types` | `leave-types.index` | `view any leave types` |
| GET | `/leave-types/create` | `leave-types.create` | `create leave types` |
| POST | `/leave-types` | `leave-types.store` | `create leave types` |
| GET | `/leave-types/{record}` | `leave-types.show` | `view any leave types` |
| GET | `/leave-types/{record}/edit` | `leave-types.edit` | `update leave types` |
| PUT | `/leave-types/{record}` | `leave-types.update` | `update leave types` |
| DELETE | `/leave-types/{record}` | `leave-types.destroy` | `delete leave types` |

> **Note**: The datatable route must be declared before `/{record}` to prevent Laravel matching `datatable` as a record ID.

---

## Database Schema

### Leave Types Table (`leave_types`)

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint unsigned | Primary key |
| `name` | varchar(255) | Leave type name |
| `code` | varchar(50) | Unique short code (AL, SL, etc.) |
| `description` | text nullable | Description |
| `is_paid` | boolean | Whether leave is paid |
| `requires_approval` | boolean | Whether approval is needed |
| `max_days_per_year` | int nullable | Maximum days allowed per year |
| `max_days_per_month` | int nullable | Maximum days allowed per month |
| `carry_forward` | boolean | Whether unused leave carries forward |
| `carry_forward_limit` | int nullable | Max days that can be carried forward |
| `carry_forward_expiry_days` | int nullable | Days after which carried forward leave expires |
| `requires_document` | boolean | Whether document is mandatory |
| `min_days_before_request` | int nullable | Min days advance notice required |
| `max_consecutive_days` | int nullable | Max consecutive days allowed |
| `is_gender_specific` | boolean | Whether leave is gender-specific |
| `applicable_gender` | enum | Applicable gender (male/female/other) |
| `is_paid_pro_rata` | boolean | Pro-rata for mid-year joiners |
| `is_active` | boolean | Status |
| `sort_order` | int | Display order |

### Holidays Table (`holidays`)

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint unsigned | Primary key |
| `name` | varchar(255) | Holiday name |
| `date` | date | Holiday date |
| `holiday_type` | enum | 'global' or 'regional' |
| `country_id` | bigint unsigned nullable | Foreign key to countries |
| `city_id` | bigint unsigned nullable | Foreign key to cities |
| `is_recurring` | boolean | Repeats yearly |
| `notes` | text nullable | Additional notes |
| `is_active` | boolean | Status |
| `sort_order` | int | Display order |

### Leave Requests Table (`leave_requests`)

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint unsigned | Primary key |
| `user_id` | bigint unsigned | Foreign key to users |
| `leave_type_id` | bigint unsigned | Foreign key to leave_types |
| `start_date` | date | Request start date |
| `end_date` | date | Request end date |
| `reason` | text nullable | Reason for leave |
| `total_days` | int | Calculated leave days |
| `status` | enum | 'pending', 'approved', 'rejected', 'cancelled' |
| `year` | int | Calendar year |
| `approved_by` | bigint unsigned nullable | Approver user_id |
| `approved_at` | timestamp nullable | Approval timestamp |
| `rejection_reason` | text nullable | Rejection reason |
| `rejected_by` | bigint unsigned nullable | Rejecter user_id |
| `rejected_at` | timestamp nullable | Rejection timestamp |
| `cancelled_at` | timestamp nullable | Cancellation timestamp |

### Leave Balances Table (`leave_balances`)

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint unsigned | Primary key |
| `user_id` | bigint unsigned | Foreign key to users |
| `leave_type_id` | bigint unsigned | Foreign key to leave_types |
| `year` | int | Calendar year |
| `total_entitlement` | int | Total days entitled |
| `taken_days` | int | Days taken |
| `remaining_days` | int | Days remaining |
| `pending_days` | int | Days in pending requests |

**Unique Constraint**: `(user_id, leave_type_id, year)`

---

## User Roles & Permissions

### Roles

| Role | Description |
|------|-------------|
| **Superuser** | Full system access, can manage all settings |
| **Admin** | Can approve/reject leave requests, manage leave types and holidays |
| **Employee** | Can create and cancel own leave requests, view own balances |

### Permissions Matrix

| Permission | Superuser | Admin | Employee |
|------------|-----------|-------|----------|
| `view any leave types` | ✓ | ✓ | ✗ |
| `create leave types` | ✓ | ✓ | ✗ |
| `update leave types` | ✓ | ✓ | ✗ |
| `delete leave types` | ✓ | ✓ | ✗ |
| `view any holidays` | ✓ | ✓ | ✓ |
| `create holidays` | ✓ | ✓ | ✗ |
| `update holidays` | ✓ | ✓ | ✗ |
| `delete holidays` | ✓ | ✓ | ✗ |
| `view any leave requests` | ✓ | ✓ | ✗ |
| `view own leave requests` | ✓ | ✓ | ✓ |
| `create leave requests` | ✓ | ✓ | ✓ |
| `approve leave requests` | ✓ | ✓ | ✗ |
| `reject leave requests` | ✓ | ✓ | ✗ |
| `cancel leave requests` | ✓ | ✓ | Own only |
| `view any leave balances` | ✓ | ✓ | ✗ |
| `view own leave balances` | ✓ | ✓ | ✓ |

---

## Leave Request Workflow

```
┌─────────────────────────────────────────────────────────────────────┐
│                        LEAVE REQUEST WORKFLOW                       │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  ┌──────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐    │
│  │  DRAFT   │───▶│ PENDING  │───▶│ APPROVED │───▶│ COMPLETED│    │
│  └──────────┘    └──────────┘    └──────────┘    └──────────┘    │
│                         │                  │                        │
│                         │                  ▼                        │
│                         │           ┌──────────┐                    │
│                         │           │ REJECTED │                    │
│                         │           └──────────┘                    │
│                         │                                           │
│                         ▼                                           │
│                    ┌──────────┐                                     │
│                    │ CANCELLED│                                     │
│                    └──────────┘                                     │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘

State Transitions:
- DRAFT → PENDING: Employee submits request
- PENDING → APPROVED: Admin/Manager approves
- PENDING → REJECTED: Admin/Manager rejects (with reason)
- PENDING → CANCELLED: Employee cancels (before approval)
- APPROVED → COMPLETED: Leave period ends (automatic)
```

### State Descriptions

| State | Description | Can Transition To |
|-------|-------------|-------------------|
| `pending` | Request submitted, awaiting approval | approved, rejected, cancelled |
| `approved` | Request approved by authorized person | completed |
| `rejected` | Request denied by authorized person | — |
| `cancelled` | Cancelled by employee before approval | — |

### Balance Impact

| State | Balance Impact |
|-------|----------------|
| `pending` | Updates `pending_days` (does not deduct from remaining) |
| `approved` | Deducts from `remaining_days`, updates `taken_days` |
| `rejected` | No balance impact |
| `cancelled` | No balance impact |

---

## Installation

### Prerequisites

- PHP 8.4+
- Laravel 12
- MySQL 8.0+ or PostgreSQL 12+
- Composer

### Setup Steps

1. **Run Migrations**
   ```bash
   php artisan migrate
   ```

2. **Seed Data**
   ```bash
   # Seed leave types
   php artisan db:seed --class=LeaveTypeSeeder

   # Seed holidays (optional)
   php artisan db:seed --class=HolidaySeeder

   # Seed permissions
   php artisan db:seed --class=PermissionSeeder
   ```

3. **Assign Permissions to Roles**
   ```bash
   php artisan tinker

   >>> $admin = Role::findByName('Admin');
   >>> $admin->givePermissionTo(['view any leave requests', 'approve leave requests', 'reject leave requests']);
   ```

---

## Usage Guide

### For Employees

#### Creating a Leave Request

1. Navigate to **Dashboard** → **My Leave Requests**
2. Click **New Request**
3. Fill in the form:
   - Select leave type
   - Choose start and end dates
   - Provide reason (optional)
4. Submit

**Note**: The system automatically calculates actual leave days excluding weekends and holidays.

#### Viewing Leave Summary

1. Navigate to **Dashboard** → **Leave Summary**
2. Select year to view breakdown
3. See entitlement, taken, remaining, and pending days per leave type

#### Cancelling a Request

1. Go to **My Leave Requests**
2. Click the **Cancel** button next to pending request
3. Confirm cancellation

### For Administrators

#### Managing Leave Types

1. Navigate to **Leave Management** → **Leave Types**
2. Create new leave types with specific rules:
   - Set maximum days per year
   - Configure carry-forward limits
   - Specify document requirements
   - Set gender restrictions if applicable

#### Managing Holidays

1. Navigate to **Leave Management** → **Holidays**
2. Add **Global Holidays** (applicable to all)
3. Add **Regional Holidays** (specific to country/city)

#### Approving/Rejecting Requests

1. Navigate to **Leave Management** → **All Leave Requests**
2. Filter by status: **Pending**
3. Review request details
4. Click **Approve** or **Reject**
5. For rejection, provide reason

---

## Routes Reference

### Leave Types
| Method | URI | Route name | Permission guard |
|--------|-----|------------|------------------|
| GET | `/leave-types/datatable` | `leave-types.datatable` | view any leave types |
| GET | `/leave-types` | `leave-types.index` | view any leave types |
| GET | `/leave-types/create` | `leave-types.create` | create leave types |
| POST | `/leave-types` | `leave-types.store` | create leave types |
| GET | `/leave-types/{record}` | `leave-types.show` | view any leave types |
| GET | `/leave-types/{record}/edit` | `leave-types.edit` | update leave types |
| PUT | `/leave-types/{record}` | `leave-types.update` | update leave types |
| DELETE | `/leave-types/{record}` | `leave-types.destroy` | delete leave types |

### Holidays
| Method | URI | Route name | Permission guard |
|--------|-----|------------|------------------|
| GET | `/holidays/datatable` | `holidays.datatable` | view any holidays |
| GET | `/holidays` | `holidays.index` | view any holidays |
| GET | `/holidays/create` | `holidays.create` | create holidays |
| POST | `/holidays` | `holidays.store` | create holidays |
| GET | `/holidays/{record}` | `holidays.show` | view any holidays |
| GET | `/holidays/{record}/edit` | `holidays.edit` | update holidays |
| PUT | `/holidays/{record}` | `holidays.update` | update holidays |
| DELETE | `/holidays/{record}` | `holidays.destroy` | delete holidays |

### Leave Requests
| Method | URI | Route name | Permission guard |
|--------|-----|------------|------------------|
| GET | `/leave-requests` | `leave-requests.index` | view any leave requests |
| POST | `/leave-requests/{record}/approve` | `leave-requests.approve` | approve leave requests |
| POST | `/leave-requests/{record}/reject` | `leave-requests.reject` | reject leave requests |
| GET | `/my-leave-requests` | `my-leave-requests.index` | view own leave requests |
| GET | `/my-leave-requests/create` | `my-leave-requests.create` | create leave requests |
| POST | `/my-leave-requests` | `my-leave-requests.store` | create leave requests |
| GET | `/my-leave-requests/{record}` | `my-leave-requests.show` | view own leave requests |
| POST | `/my-leave-requests/{record}/cancel` | `my-leave-requests.cancel` | cancel leave requests |

---

## Testing

### Run Tests
```bash
# All tests
php artisan test --compact

# Leave Type tests only
php artisan test --compact --filter=LeaveType
```

---

## Troubleshooting

**DataTable not loading** — Ensure the `/datatable` route is declared before `/{record}` in `routes/web.php`, otherwise Laravel matches the string `"datatable"` as a record ID.

**Select2 not initialising on form selects** — Fields must have `data-toggle="select2"` on the `<select>` element (not `data-select2`). The global JS initialiser in `bootstrap.js` queries `select[data-toggle="select2"]`.

**Validation ignoring current record on update** — `UpdateLeaveTypeRequest` reads the route parameter via `$this->route('record')`. If the route parameter name changes, update this line accordingly.

---

**Version**: 1.1.0
**Last Updated**: March 2026
