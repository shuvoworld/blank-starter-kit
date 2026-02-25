# Leave & Attendance Management System

## Overview

The Leave & Attendance Management System is a comprehensive Laravel-based module that enables organizations to manage employee leave requests, track leave balances, and maintain holiday calendars. The system supports configurable leave types, automated leave calculations, and role-based access control.

## Table of Contents

- [Features](#features)
- [Architecture](#architecture)
- [Database Schema](#database-schema)
- [User Roles & Permissions](#user-roles--permissions)
- [Leave Request Workflow](#leave-request-workflow)
- [Installation](#installation)
- [Usage Guide](#usage-guide)
- [API Reference](#api-reference)
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

### 6. Employee-User Association
- Link employee records to system user accounts
- Role-based access control for user association

---

## Architecture

### Directory Structure

```
app/
├── Models/
│   ├── LeaveType.php          # Leave type definition
│   ├── Holiday.php            # Holiday calendar
│   ├── LeaveRequest.php       # Leave request with states
│   └── LeaveBalance.php       # Balance tracking
├── Http/
│   ├── Controllers/
│   │   ├── LeaveTypeController.php
│   │   ├── HolidayController.php
│   │   ├── LeaveRequestController.php
│   │   └── LeaveBalanceController.php
│   └── Requests/
│       ├── StoreLeaveRequestRequest.php
│       ├── ApproveLeaveRequestRequest.php
│       └── RejectLeaveRequestRequest.php
├── Services/
│   ├── LeaveCalculationService.php    # Business logic for day calculations
│   └── LeaveBalanceService.php        # Balance management
└── ...

resources/views/
├── leave-types/               # Leave type CRUD views
├── holidays/                  # Holiday CRUD views
├── leave-requests/            # Leave request & workflow views
├── leave-balances/            # Leave summary views
└── dashboards/
    └── employee.blade.php     # Employee dashboard

database/
├── migrations/
│   ├── *_create_leave_types_table.php
│   ├── *_create_holidays_table.php
│   ├── *_create_leave_requests_table.php
│   └── *_create_leave_balances_table.php
└── seeders/
    ├── LeaveTypeSeeder.php
    └── HolidaySeeder.php
```

### Design Patterns

1. **Service Layer**: Business logic encapsulated in service classes
2. **State Machine**: Leave request status transitions
3. **Repository Pattern**: Eloquent models for data access
4. **Factory Pattern**: Model factories for testing
5. **Observer Pattern**: Activity logging for audit trails

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

## API Reference

### Leave Calculation Service

#### `calculateActualDays(Carbon $startDate, Carbon $endDate, ?int $countryId, ?int $cityId): int`

Calculates actual leave days excluding weekends and holidays.

```php
use App\Services\LeaveCalculationService;

$service = new LeaveCalculationService();
$days = $service->calculateActualDays(
    Carbon::parse('2026-03-01'),
    Carbon::parse('2026-03-07'),
    $employee->country_id,
    $employee->city_id
);
// Returns: 5 (excludes Saturday & Sunday)
```

#### `getHolidaysInRange(Carbon $startDate, Carbon $endDate, ?int $countryId, ?int $cityId): Collection`

Returns all holidays within a date range for a specific location.

```php
$holidays = $service->getHolidaysInRange(
    Carbon::parse('2026-12-01'),
    Carbon::parse('2026-12-31'),
    $countryId,
    $cityId
);
```

### Leave Balance Service

#### `getOrCreateBalance(int $userId, int $leaveTypeId, int $year): LeaveBalance`

Gets existing balance or creates new one with pro-rata calculation.

```php
use App\Services\LeaveBalanceService;

$service = new LeaveBalanceService();
$balance = $service->getOrCreateBalance($userId, $leaveTypeId, 2026);
```

#### `getLeaveSummary(int $userId, int $year): array`

Returns complete leave summary for a user.

```php
$summary = $service->getLeaveSummary($userId, 2026);
// Returns:
// [
//     'year' => 2026,
//     'total_entitlement' => 36,
//     'total_taken' => 5,
//     'total_remaining' => 31,
//     'total_pending' => 2,
//     'usage_percentage' => 13.89,
//     'by_type' => [...]
// ]
```

---

## Routes

### Leave Types
| Method | URI | Name | Permission |
|--------|-----|------|------------|
| GET | `/leave-types` | `leave-types.index` | view any leave types |
| GET | `/leave-types/create` | `leave-types.create` | create leave types |
| POST | `/leave-types` | `leave-types.store` | create leave types |
| GET | `/leave-types/{leaveType}` | `leave-types.show` | view any leave types |
| GET | `/leave-types/{leaveType}/edit` | `leave-types.edit` | update leave types |
| PUT | `/leave-types/{leaveType}` | `leave-types.update` | update leave types |
| DELETE | `/leave-types/{leaveType}` | `leave-types.destroy` | delete leave types |

### Holidays
| Method | URI | Name | Permission |
|--------|-----|------|------------|
| GET | `/holidays` | `holidays.index` | view any holidays |
| GET | `/holidays/create` | `holidays.create` | create holidays |
| POST | `/holidays` | `holidays.store` | create holidays |
| GET | `/holidays/{holiday}` | `holidays.show` | view any holidays |
| GET | `/holidays/{holiday}/edit` | `holidays.edit` | update holidays |
| PUT | `/holidays/{holiday}` | `holidays.update` | update holidays |
| DELETE | `/holidays/{holiday}` | `holidays.destroy` | delete holidays |

### Leave Requests
| Method | URI | Name | Permission |
|--------|-----|------|------------|
| GET | `/leave-requests` | `leave-requests.index` | view any leave requests |
| POST | `/leave-requests/{leaveRequest}/approve` | `leave-requests.approve` | approve leave requests |
| POST | `/leave-requests/{leaveRequest}/reject` | `leave-requests.reject` | reject leave requests |
| GET | `/my-leave-requests` | `my-leave-requests.index` | view own leave requests |
| GET | `/my-leave-requests/create` | `my-leave-requests.create` | create leave requests |
| POST | `/my-leave-requests` | `my-leave-requests.store` | create leave requests |
| GET | `/my-leave-requests/{leaveRequest}` | `my-leave-requests.show` | view own leave requests |
| POST | `/my-leave-requests/{leaveRequest}/cancel` | `my-leave-requests.cancel` | cancel leave requests |

### Leave Balances
| Method | URI | Name | Permission |
|--------|-----|------|------------|
| GET | `/my-leave-summary` | `my-leave-summary` | view own leave balances |
| GET | `/api/leave-balance` | `api.leave-balance` | view own leave balances |

---

## Testing

### Run All Tests
```bash
php artisan test
```

### Run Specific Test Suite
```bash
# Leave Type tests
php artisan test --filter=LeaveTypeTest

# Leave Request tests
php artisan test --filter=LeaveRequestTest

# Leave Calculation tests
php artisan test --filter=LeaveCalculationTest
```

### Test Coverage

The test suite covers:
- Leave type CRUD operations
- Holiday CRUD operations
- Leave request workflow (submit, approve, reject, cancel)
- Balance calculations and updates
- Pro-rata calculations for mid-year joiners
- Weekend and holiday exclusions
- Permission checks

---

## Troubleshooting

### Common Issues

**Issue**: Leave balance not updating after approval
- **Solution**: Check that `LeaveBalanceService::approveRequest()` is being called in the approval process

**Issue**: Holidays not being excluded from leave calculation
- **Solution**: Ensure `holiday_type` is set correctly and `is_active` is true for holiday records

**Issue**: Pending requests showing incorrect balance
- **Solution**: Run `LeaveBalanceService::updatePendingDays()` to recalculate

**Issue**: Pro-rata calculation not working
- **Solution**: Verify `is_paid_pro_rata` is enabled for the leave type and user has a valid join date

---

## Support & Contributing

For bug reports, feature requests, or contributions, please refer to the project's main documentation.

---

**Version**: 1.0.0
**Last Updated**: February 2026
