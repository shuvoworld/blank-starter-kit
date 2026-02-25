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
            'Superuser' => 'superuser',
            'Admin' => 'admin',
            'Employee' => 'employee',
        ];

        // Find the first matching role (higher priority roles first)
        $dashboardView = 'dashboards.employee'; // default fallback
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

        // TODO: Add schedules when Schedule model is created
        // if ($user->can('view any schedules')) {
        //     $stats['schedules'] = \App\Models\Schedule::count();
        // }

        // TODO: Add shifts when Shift model is created
        // if ($user->can('view any shifts')) {
        //     $stats['shifts'] = \App\Models\Shift::count();
        // }

        if ($user->can('view any roles')) {
            $stats['roles'] = \App\Models\Role::count();
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
