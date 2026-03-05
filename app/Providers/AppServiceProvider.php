<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Set default pagination view to Bootstrap 5
        Paginator::defaultView('vendor.pagination.bootstrap-5');
        Paginator::defaultSimpleView('vendor.pagination.bootstrap-5');

        // Superusers bypass all Gate and permission checks globally.
        // Returning true here short-circuits every policy, gate, and
        // Spatie permission middleware check for the Superuser role.
        Gate::before(function (User $user, string $ability): ?bool {
            if ($user->hasRole('Superuser')) {
                return true;
            }

            return null;
        });
    }
}
