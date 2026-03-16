<?php

use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\Permission;
use App\Models\User;
use App\Services\EmailService;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    Permission::firstOrCreate(['name' => 'leave-requests.create', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'leave-requests.approve', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'leave-requests.view', 'guard_name' => 'web']);
});

/**
 * Creates a user + employee + leave type + balance ready for a store() call.
 * Returns ['user' => User, 'leaveType' => LeaveType].
 */
function makeEmployeeWithLeave(): array
{
    $user = User::factory()->create();
    $user->givePermissionTo('leave-requests.create');
    Employee::factory()->create(['user_id' => $user->id]);

    $leaveType = LeaveType::create([
        'name' => 'Annual Leave',
        'code' => 'AL',
        'is_paid' => true,
        'requires_approval' => true,
        'max_days_per_year' => 20,
        'is_active' => true,
        'sort_order' => 1,
    ]);

    LeaveBalance::create([
        'user_id' => $user->id,
        'leave_type_id' => $leaveType->id,
        'year' => now()->year,
        'total_days' => 20,
        'used_days' => 0,
        'pending_days' => 0,
        'remaining_days' => 20,
        'carried_forward_days' => 0,
    ]);

    return ['user' => $user, 'leaveType' => $leaveType];
}

it('sends a confirmation email to the employee when a leave request is submitted', function () {
    $emailService = Mockery::mock(EmailService::class);
    app()->instance(EmailService::class, $emailService);

    ['user' => $employee, 'leaveType' => $leaveType] = makeEmployeeWithLeave();

    $emailService->shouldReceive('send')
        ->once()
        ->withArgs(fn ($to, $subject) => $to === $employee->email
            && $subject === 'Your Leave Request Has Been Submitted');

    $emailService->shouldReceive('send')->zeroOrMoreTimes();

    actingAs($employee)->post(route('my-leave-requests.store'), [
        'leave_type_id' => $leaveType->id,
        'start_date' => now()->addDays(5)->toDateString(),
        'end_date' => now()->addDays(6)->toDateString(),
        'reason' => 'Family trip',
    ])->assertRedirect(route('my-leave-requests.index'));
});

it('sends a notification email to each approver when a leave request is submitted', function () {
    $emailService = Mockery::mock(EmailService::class);
    app()->instance(EmailService::class, $emailService);

    ['user' => $employee, 'leaveType' => $leaveType] = makeEmployeeWithLeave();

    $approver1 = User::factory()->create();
    $approver1->givePermissionTo('leave-requests.approve');

    $approver2 = User::factory()->create();
    $approver2->givePermissionTo('leave-requests.approve');

    $emailService->shouldReceive('send')
        ->withArgs(fn ($to) => $to === $approver1->email)
        ->once();

    $emailService->shouldReceive('send')
        ->withArgs(fn ($to) => $to === $approver2->email)
        ->once();

    $emailService->shouldReceive('send')->zeroOrMoreTimes();

    actingAs($employee)->post(route('my-leave-requests.store'), [
        'leave_type_id' => $leaveType->id,
        'start_date' => now()->addDays(5)->toDateString(),
        'end_date' => now()->addDays(6)->toDateString(),
    ])->assertRedirect(route('my-leave-requests.index'));
});

it('stores the leave request even if email sending fails', function () {
    $emailService = Mockery::mock(EmailService::class);
    $emailService->shouldReceive('send')->andThrow(new \RuntimeException('SMTP down'));
    app()->instance(EmailService::class, $emailService);

    ['user' => $employee, 'leaveType' => $leaveType] = makeEmployeeWithLeave();

    actingAs($employee)->post(route('my-leave-requests.store'), [
        'leave_type_id' => $leaveType->id,
        'start_date' => now()->addDays(5)->toDateString(),
        'end_date' => now()->addDays(6)->toDateString(),
    ])->assertRedirect(route('my-leave-requests.index'));

    expect(\App\Models\LeaveRequest::count())->toBe(1);
});
