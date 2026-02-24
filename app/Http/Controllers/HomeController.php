<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the user's dashboard based on their role.
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        // Get the user's roles
        $roles = $user->roles->pluck('name')->toArray();

        // Define role-to-dashboard mapping with priority order
        $roleDashboardMap = [
            'Super Admin' => 'superadmin',
            'Admin' => 'admin',
            'Manager' => 'manager',
            'Editor' => 'editor',
            'Employee' => 'employee',
            'User' => 'user',
        ];

        // Find the first matching role (higher priority roles first)
        $dashboardView = 'dashboards.user'; // default fallback
        foreach ($roleDashboardMap as $role => $view) {
            if (in_array($role, $roles)) {
                $dashboardView = "dashboards.{$view}";
                break;
            }
        }

        // Prepare common data for all dashboards
        $stats = $this->getStats($user);
        $recentActivities = $this->getRecentActivities();

        return view($dashboardView, [
            'stats' => $stats,
            'recentActivities' => $recentActivities,
            'userRoles' => $user->roles,
        ]);
    }

    /**
     * Get statistics based on user permissions.
     */
    protected function getStats($user): array
    {
        $stats = [];

        if ($user->can('view any users')) {
            $stats['users'] = \App\Models\User::count();
        }

        if ($user->can('view any employees')) {
            $stats['employees'] = \App\Models\Employee::count();
            $stats['activeEmployees'] = \App\Models\Employee::where('status', 'active')->count();
            $stats['recentEmployees'] = \App\Models\Employee::latest()->limit(10)->get();
        }

        if ($user->can('view any products')) {
            $stats['products'] = \App\Models\Product::count();
            $stats['inStockProducts'] = \App\Models\Product::where('stock', '>', 0)->count();
            $stats['recentProducts'] = \App\Models\Product::latest()->limit(5)->get();
        }

        if ($user->can('view any roles')) {
            $stats['roles'] = \App\Models\Role::count();
        }

        if ($user->can('view any permissions')) {
            $stats['permissions'] = \App\Models\Permission::count();
        }

        // Landing page stats for those with permission
        if ($user->can('manage landing page')) {
            $stats['landingSections'] = \App\Models\LandingPageSection::count();
        }

        return $stats;
    }

    /**
     * Get recent activities based on user permissions.
     */
    protected function getRecentActivities()
    {
        if (!auth()->user()->can('view any activity log')) {
            return collect();
        }

        return \Spatie\Activitylog\Models\Activity::with('causer')
            ->latest()
            ->limit(10)
            ->get();
    }
}
