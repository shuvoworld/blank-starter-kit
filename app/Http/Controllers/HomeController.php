<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use App\Models\LandingPageSection;
use App\Services\LeaveBalanceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(private LeaveBalanceService $balanceService) {}

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
        $userRole = 'employee';
        foreach ($roleDashboardMap as $role => $view) {

            if (in_array($role, $roles)) {
                $dashboardView = "dashboards.{$view}";
                $userRole = $role;
                break;
            }
        }

        // Prepare common data for all dashboards
        $stats = $this->getStats($user);
        $recentActivities = $this->getRecentActivities();

        // Get role-specific dashboard data
        $dashboardData = match ($userRole) {
            'Superuser', 'Admin' => $this->getAdminDashboardData(),
            'Employee' => $this->getEmployeeDashboardData($user),
            default => [],
        };

        return view($dashboardView, array_merge(
            [
                'stats' => $stats,
                'recentActivities' => $recentActivities,
                'userRoles' => $user->roles,
                'userRole' => $userRole,
            ],
            $dashboardData
        ));
    }

    /**
     * Get admin-specific dashboard data.
     */
    protected function getAdminDashboardData(): array
    {
        $currentYear = now()->year;

        // Get pending leave requests that need attention
        $pendingRequests = \App\Models\LeaveRequest::with(['user', 'leaveType'])
            ->pending()
            ->orderBy('created_at', 'asc')
            ->limit(10)
            ->get();

        // Get recently approved requests
        $recentlyApproved = \App\Models\LeaveRequest::with(['user', 'leaveType', 'approvedBy'])
            ->approved()
            ->orderBy('approved_at', 'desc')
            ->limit(5)
            ->get();

        // Get upcoming holidays (next 60 days)
        $upcomingHolidays = Holiday::active()
            ->whereBetween('date', [now(), now()->addDays(60)])
            ->orderBy('date')
            ->get();

        // Get leave types count
        $leaveTypesCount = \App\Models\LeaveType::active()->count();

        // Get holidays count for current year
        $holidaysCount = Holiday::whereYear('date', $currentYear)->active()->count();

        // Get leave requests stats for current year
        $leaveRequestsStats = [
            'total' => \App\Models\LeaveRequest::where('year', $currentYear)->count(),
            'pending' => \App\Models\LeaveRequest::where('year', $currentYear)->pending()->count(),
            'approved' => \App\Models\LeaveRequest::where('year', $currentYear)->approved()->count(),
            'rejected' => \App\Models\LeaveRequest::where('year', $currentYear)->rejected()->count(),
            'cancelled' => \App\Models\LeaveRequest::where('year', $currentYear)->cancelled()->count(),
        ];

        return [
            'pendingRequests' => $pendingRequests,
            'recentlyApproved' => $recentlyApproved,
            'upcomingHolidays' => $upcomingHolidays,
            'leaveTypesCount' => $leaveTypesCount,
            'holidaysCount' => $holidaysCount,
            'leaveRequestsStats' => $leaveRequestsStats,
            'currentYear' => $currentYear,
        ];
    }

    /**
     * Get employee-specific dashboard data.
     */
    protected function getEmployeeDashboardData($user): array
    {
        $currentYear = now()->year;

        // Get leave balances for current year
        $leaveBalances = $this->balanceService->getUserBalances($user->id, $currentYear);

        // Get pending leave requests
        $pendingRequests = $user->leaveRequests()
            ->with('leaveType')
            ->pending()
            ->where('year', $currentYear)
            ->orderBy('start_date')
            ->limit(5)
            ->get();

        // Get upcoming holidays (next 30 days)
        $upcomingHolidays = Holiday::active()
            ->whereBetween('date', [now(), now()->addDays(30)])
            ->orderBy('date')
            ->get();

        // Calculate leave stats
        $leaveStats = [
            'total_entitlement' => $leaveBalances->sum('total_entitlement'),
            'total_taken' => $leaveBalances->sum('taken_days'),
            'remaining' => $leaveBalances->sum('remaining_days'),
            'pending' => $leaveBalances->sum('pending_days'),
        ];

        return [
            'leaveBalances' => $leaveBalances,
            'pendingRequests' => $pendingRequests,
            'upcomingHolidays' => $upcomingHolidays,
            'leaveStats' => $leaveStats,
            'currentYear' => $currentYear,
        ];
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

        if ($user->can('view any leave requests')) {
            $stats['pendingRequests'] = \App\Models\LeaveRequest::pending()->count();
            $stats['approvedToday'] = \App\Models\LeaveRequest::approved()
                ->whereDate('approved_at', today())
                ->count();
        }

        if ($user->can('view any leave balances')) {
            $currentYear = now()->year;
            $stats['usersWithBalances'] = \App\Models\LeaveBalance::where('year', $currentYear)
                ->distinct('user_id')
                ->count();
        }

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
        if (! auth()->user()->can('view any activity log')) {
            return collect();
        }

        return \Spatie\Activitylog\Models\Activity::with('causer')
            ->latest()
            ->limit(10)
            ->get();
    }

    public function publicPage()
    {
        // Get all landing page sections grouped by section
        $sections = LandingPageSection::getGroupedBySection();

        // Helper function to get section value
        $getValue = fn (string $key, mixed $default = '') => LandingPageSection::getValue($key, $default);

        return view('public.welcome', [
            'sections' => $sections,
            'getValue' => $getValue,
        ]);
    }
}
