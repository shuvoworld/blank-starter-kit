<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DepartmentDataTableController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\DesignationDataTableController;
use App\Http\Controllers\EmployeeCategoryController;
use App\Http\Controllers\EmployeeCategoryDataTableController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeDataTableController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\HolidayDataTableController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LandingPageSectionController;
use App\Http\Controllers\LandingPageSectionDataTableController;
use App\Http\Controllers\LeaveBalanceController;
use App\Http\Controllers\LeaveBalanceDataTableController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\LeaveRequestDataTableController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\LeaveTypeDataTableController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PermissionDataTableController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductDataTableController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RoleDataTableController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserDataTableController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', HomeController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/', [HomeController::class, 'publicPage'])
    ->name('public');

Route::middleware('auth')->group(function () {

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Standard CRUD modules
    Route::crudModule('users', UserController::class, UserDataTableController::class);
    Route::crudModule('departments', DepartmentController::class, DepartmentDataTableController::class);
    Route::crudModule('designations', DesignationController::class, DesignationDataTableController::class);
    Route::crudModule('products', ProductController::class, ProductDataTableController::class);
    Route::crudModule('leave-types', LeaveTypeController::class, LeaveTypeDataTableController::class);

    // Roles — standard CRUD + permission assignment routes
    Route::crudModule('roles', RoleController::class, RoleDataTableController::class, function () {
        Route::post('/{role}/permissions', [RoleController::class, 'assignPermissions'])
            ->name('permissions.assign')
            ->middleware('permission:update roles');
        Route::delete('/{role}/permissions/{permission}', [RoleController::class, 'removePermission'])
            ->name('permissions.remove')
            ->middleware('permission:update roles');
    });

    // Permissions — standard CRUD + by-module lookup
    Route::crudModule('permissions', PermissionController::class, PermissionDataTableController::class, function () {
        Route::get('/by-module', [PermissionController::class, 'getByModule'])->name('by-module');
    });

    // Employees — standard CRUD + location lookup routes
    Route::crudModule('employees', EmployeeController::class, EmployeeDataTableController::class, function () {
        Route::get('/cities/by-country', [EmployeeController::class, 'citiesByCountry'])->name('cities.by-country');
        Route::get('/areas/by-city', [EmployeeController::class, 'areasByCity'])->name('areas.by-city');
    });

    // Holidays — standard CRUD + public API for date-range lookup
    Route::crudModule('holidays', HolidayController::class, HolidayDataTableController::class, function () {
        Route::get('/api/dates', [HolidayController::class, 'getHolidaysForDateRange'])->name('api.dates');
    });

    Route::crudModule('employee-categories', EmployeeCategoryController::class, EmployeeCategoryDataTableController::class);

    // Leave Requests — admin view (no create/edit; approve/reject/cancel actions)
    Route::prefix('leave-requests')->name('leave-requests.')->middleware('permission:view any leave requests')->group(function () {
        Route::get('/datatable', [LeaveRequestDataTableController::class, 'datatable'])->name('datatable');
        Route::get('', [LeaveRequestController::class, 'index'])->name('index');
        Route::get('/{record}', [LeaveRequestController::class, 'show'])->name('show');
        Route::post('/{record}/approve', [LeaveRequestController::class, 'approve'])->name('approve')->middleware('permission:approve leave requests');
        Route::post('/{record}/reject', [LeaveRequestController::class, 'reject'])->name('reject')->middleware('permission:reject leave requests');
        Route::post('/{record}/cancel', [LeaveRequestController::class, 'cancel'])->name('cancel')->middleware('permission:cancel leave requests');
    });

    // My Leave Requests — employee's own requests
    Route::prefix('my-leave-requests')->name('my-leave-requests.')->group(function () {
        Route::get('', [LeaveRequestController::class, 'myRequests'])->name('index');
        Route::get('/create', [LeaveRequestController::class, 'create'])->name('create');
        Route::post('/', [LeaveRequestController::class, 'store'])->name('store');
        Route::get('/{record}', [LeaveRequestController::class, 'showMy'])->name('show');
        Route::post('/{record}/cancel', [LeaveRequestController::class, 'cancel'])->name('cancel');
    });

    // Leave Balances — admin view + employee summary
    Route::prefix('leave-balances')->name('leave-balances.')->middleware('permission:view any leave balances')->group(function () {
        Route::get('/datatable', [LeaveBalanceDataTableController::class, 'datatable'])->name('datatable');
        Route::get('/summary', [LeaveBalanceController::class, 'userSummary'])->name('summary');
        Route::get('', [LeaveBalanceController::class, 'index'])->name('index');
    });

    Route::post('/leave-balances/get-balance', [LeaveBalanceController::class, 'getBalance'])->name('leave-balances.get-balance');
    Route::get('/my-leave-summary', [LeaveBalanceController::class, 'mySummary'])->name('my-leave-summary');

    // Landing Page Sections — edit/update only
    Route::prefix('landing-page-sections')->name('landing-page-sections.')->middleware('permission:manage landing page')->group(function () {
        Route::get('/datatable', [LandingPageSectionDataTableController::class, 'datatable'])->name('datatable');
        Route::get('', [LandingPageSectionController::class, 'index'])->name('index');
        Route::get('/{record}/edit', [LandingPageSectionController::class, 'edit'])->name('edit');
        Route::put('/{record}', [LandingPageSectionController::class, 'update'])->name('update');
    });

    // Activity Log — view only
    Route::prefix('activity-log')->name('activity-log.')->middleware('permission:view any activity log')->group(function () {
        Route::get('/', [ActivityLogController::class, 'index'])->name('index');
        Route::get('/{activity}', [ActivityLogController::class, 'show'])->name('show');
    });
});

require __DIR__.'/auth.php';
