<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LandingPageSectionController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', HomeController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // User Routes
    Route::resource('users', UserController::class);
    Route::get('/users/{user}/roles', [UserController::class, 'editRoles'])->name('users.roles');
    Route::put('/users/{user}/roles', [UserController::class, 'updateRoles'])->name('users.roles.update');

    // Role Routes - Protected by permission middleware
    Route::prefix('roles')
        ->name('roles.')
        ->middleware('permission:view any roles')
        ->group(function () {
            Route::get('/', [RoleController::class, 'index'])->name('index');
            Route::post('/', [RoleController::class, 'store'])->name('store')->middleware('permission:create roles');
            Route::get('/create', [RoleController::class, 'create'])->name('create')->middleware('permission:create roles');
            Route::get('/{role}', [RoleController::class, 'show'])->name('show');
            Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit')->middleware('permission:update roles');
            Route::put('/{role}', [RoleController::class, 'update'])->name('update')->middleware('permission:update roles');
            Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy')->middleware('permission:delete roles');
            Route::post('/{role}/permissions', [RoleController::class, 'assignPermissions'])->name('permissions.assign')->middleware('permission:update roles');
            Route::delete('/{role}/permissions/{permission}', [RoleController::class, 'removePermission'])->name('permissions.remove')->middleware('permission:update roles');
        });

    // Permission Routes - Protected by permission middleware
    Route::prefix('permissions')
        ->name('permissions.')
        ->middleware('permission:view any permissions')
        ->group(function () {
            Route::get('/', [PermissionController::class, 'index'])->name('index');
            Route::post('/', [PermissionController::class, 'store'])->name('store')->middleware('permission:create permissions');
            Route::get('/create', [PermissionController::class, 'create'])->name('create')->middleware('permission:create permissions');
            Route::get('/{permission}', [PermissionController::class, 'show'])->name('show');
            Route::get('/{permission}/edit', [PermissionController::class, 'edit'])->name('edit')->middleware('permission:update permissions');
            Route::put('/{permission}', [PermissionController::class, 'update'])->name('update')->middleware('permission:update permissions');
            Route::delete('/{permission}', [PermissionController::class, 'destroy'])->name('destroy')->middleware('permission:delete permissions');
            Route::get('/by-module', [PermissionController::class, 'getByModule'])->name('by-module');
        });

    // Employee Routes
    Route::prefix('employees')->name('employees.')->middleware('permission:view any employees')->group(function () {
        Route::get('/', [EmployeeController::class, 'index'])->name('index');
        Route::get('/create', [EmployeeController::class, 'create'])->name('create')->middleware('permission:create employees');
        Route::post('/', [EmployeeController::class, 'store'])->name('store')->middleware('permission:create employees');
        Route::get('/{employee}', [EmployeeController::class, 'show'])->name('show');
        Route::get('/{employee}/edit', [EmployeeController::class, 'edit'])->name('edit')->middleware('permission:update employees');
        Route::put('/{employee}', [EmployeeController::class, 'update'])->name('update')->middleware('permission:update employees');
        Route::delete('/{employee}', [EmployeeController::class, 'destroy'])->name('destroy')->middleware('permission:delete employees');
    });

    // Department Routes
    Route::prefix('departments')->name('departments.')->middleware('permission:view any departments')->group(function () {
        Route::get('/', [DepartmentController::class, 'index'])->name('index');
        Route::get('/create', [DepartmentController::class, 'create'])->name('create')->middleware('permission:create departments');
        Route::post('/', [DepartmentController::class, 'store'])->name('store')->middleware('permission:create departments');
        Route::get('/{department}', [DepartmentController::class, 'show'])->name('show');
        Route::get('/{department}/edit', [DepartmentController::class, 'edit'])->name('edit')->middleware('permission:update departments');
        Route::put('/{department}', [DepartmentController::class, 'update'])->name('update')->middleware('permission:update departments');
        Route::delete('/{department}', [DepartmentController::class, 'destroy'])->name('destroy')->middleware('permission:delete departments');
    });

    // Designation Routes
    Route::prefix('designations')->name('designations.')->middleware('permission:view any designations')->group(function () {
        Route::get('/', [DesignationController::class, 'index'])->name('index');
        Route::get('/create', [DesignationController::class, 'create'])->name('create')->middleware('permission:create designations');
        Route::post('/', [DesignationController::class, 'store'])->name('store')->middleware('permission:create designations');
        Route::get('/{designation}', [DesignationController::class, 'show'])->name('show');
        Route::get('/{designation}/edit', [DesignationController::class, 'edit'])->name('edit')->middleware('permission:update designations');
        Route::put('/{designation}', [DesignationController::class, 'update'])->name('update')->middleware('permission:update designations');
        Route::delete('/{designation}', [DesignationController::class, 'destroy'])->name('destroy')->middleware('permission:delete designations');
    });

    // Activity Log Routes
    Route::prefix('activity-log')->name('activity-log.')->middleware('permission:view any activity log')->group(function () {
        Route::get('/', [ActivityLogController::class, 'index'])->name('index');
        Route::get('/{activity}', [ActivityLogController::class, 'show'])->name('show');
    });

    // Product Routes
    Route::prefix('products')->name('products.')->middleware('permission:view any products')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/create', [ProductController::class, 'create'])->name('create')->middleware('permission:create products');
        Route::post('/', [ProductController::class, 'store'])->name('store')->middleware('permission:create products');
        Route::get('/{product}', [ProductController::class, 'show'])->name('show');
        Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit')->middleware('permission:update products');
        Route::put('/{product}', [ProductController::class, 'update'])->name('update')->middleware('permission:update products');
        Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy')->middleware('permission:delete products');
    });

    // Landing Page Section Routes
    Route::prefix('landing-page-sections')
        ->name('landing-page-sections.')
        ->middleware('permission:manage landing page')
        ->group(function () {
            Route::get('/', [LandingPageSectionController::class, 'index'])->name('index');
            Route::get('/{landing_page_section}/edit', [LandingPageSectionController::class, 'edit'])->name('edit');
            Route::put('/{landing_page_section}', [LandingPageSectionController::class, 'update'])->name('update');
        });
});

require __DIR__.'/auth.php';
