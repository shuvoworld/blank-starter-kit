<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public function register(): void {}

    /**
     * Bootstrap services.
     *
     * Registers the `Route::crudModule()` macro used in routes/web.php to
     * avoid repeating the standard CRUD + DataTable route group for every module.
     *
     * Permission names use dot-notation matching the seeder and policies
     * (e.g. prefix "leave-types" → "leave-types.view", "leave-types.create", etc.).
     *
     * Usage:
     *   // Standard module
     *   Route::crudModule('departments', DepartmentController::class, DepartmentDataTableController::class);
     *
     *   // Module with extra routes
     *   Route::crudModule('roles', RoleController::class, RoleDataTableController::class, function () {
     *       Route::post('/{role}/permissions', [RoleController::class, 'assignPermissions'])
     *           ->name('permissions.assign')
     *           ->middleware('permission:roles.update');
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
            Route::prefix($prefix)
                ->name($prefix.'.')
                ->middleware("permission:{$prefix}.view")
                ->group(function () use ($controller, $dtController, $prefix, $extras): void {
                    if ($dtController) {
                        Route::get('/datatable', [$dtController, 'datatable'])->name('datatable');
                    }

                    Route::get('/', [$controller, 'index'])->name('index');

                    Route::get('/create', [$controller, 'create'])
                        ->name('create')
                        ->middleware("permission:{$prefix}.create");

                    Route::post('/', [$controller, 'store'])
                        ->name('store')
                        ->middleware("permission:{$prefix}.create");

                    // Extra static routes must be registered before wildcard {record} routes
                    // to prevent /prefix/static-path from being matched by /{record}.
                    if ($extras) {
                        $extras();
                    }

                    Route::get('/{record}', [$controller, 'show'])->name('show');

                    Route::get('/{record}/edit', [$controller, 'edit'])
                        ->name('edit')
                        ->middleware("permission:{$prefix}.update");

                    Route::put('/{record}', [$controller, 'update'])
                        ->name('update')
                        ->middleware("permission:{$prefix}.update");

                    Route::delete('/{record}', [$controller, 'destroy'])
                        ->name('destroy')
                        ->middleware("permission:{$prefix}.delete");
                });
        });
    }
}
