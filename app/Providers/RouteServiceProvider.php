<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class RouteServiceProvider extends ServiceProvider
{
    public function register(): void {}

    /**
     * Bootstrap services.
     *
     * Registers the `Route::crudModule()` macro used in routes/web.php to
     * avoid repeating the standard CRUD + DataTable route group for every module.
     *
     * The permission slug is auto-derived from the prefix by replacing hyphens
     * with spaces (e.g. "leave-types" → "leave types").
     *
     * Usage:
     *   // Standard module
     *   Route::crudModule('departments', DepartmentController::class, DepartmentDataTableController::class);
     *
     *   // Module with extra routes
     *   Route::crudModule('roles', RoleController::class, RoleDataTableController::class, function () {
     *       Route::post('/{role}/permissions', [RoleController::class, 'assignPermissions'])
     *           ->name('permissions.assign')
     *           ->middleware('permission:update roles');
     *   });
     *
     * @param  string  $prefix  URL prefix and route name prefix (e.g. 'departments')
     * @param  class-string  $controller  Main CRUD controller class
     * @param  class-string|null  $dtController  DataTable controller class (null = no datatable route)
     * @param  \Closure|null  $extras  Optional closure for additional routes inside the group
     */
    public function boot(): void
    {
        Route::macro('crudModule', function (
            string $prefix,
            string $controller,
            ?string $dtController = null,
            ?\Closure $extras = null
        ): void {
            $permissionName = Str::replace('-', ' ', $prefix);

            Route::prefix($prefix)
                ->name($prefix.'.')
                ->middleware("permission:view any {$permissionName}")
                ->group(function () use ($controller, $dtController, $permissionName, $extras): void {
                    if ($dtController) {
                        Route::get('/datatable', [$dtController, 'datatable'])->name('datatable');
                    }

                    Route::get('/', [$controller, 'index'])->name('index');

                    Route::get('/create', [$controller, 'create'])
                        ->name('create')
                        ->middleware("permission:create {$permissionName}");

                    Route::post('/', [$controller, 'store'])
                        ->name('store')
                        ->middleware("permission:create {$permissionName}");

                    Route::get('/{record}', [$controller, 'show'])->name('show');

                    Route::get('/{record}/edit', [$controller, 'edit'])
                        ->name('edit')
                        ->middleware("permission:update {$permissionName}");

                    Route::put('/{record}', [$controller, 'update'])
                        ->name('update')
                        ->middleware("permission:update {$permissionName}");

                    Route::delete('/{record}', [$controller, 'destroy'])
                        ->name('destroy')
                        ->middleware("permission:delete {$permissionName}");

                    if ($extras) {
                        $extras();
                    }
                });
        });
    }
}
