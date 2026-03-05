<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController\BaseController;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Services\LeaveBalanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveBalanceController extends BaseController
{
    public function __construct(
        LeaveBalanceDataTableController $dataTableController,
        private LeaveBalanceService $balanceService
    ) {
        $this->model = LeaveBalance::class;
        $this->routePrefix = 'leave-balances';
        $this->viewPrefix = 'leave-balances';
        $this->resourceName = 'Leave Balance';
        $this->dataTableController = $dataTableController;
    }

    public function index(Request $request): View
    {
        $this->authorizeAction('viewAny');

        $tableColumns = $this->dataTableController?->tableColumns() ?? [];
        $dtColumns = collect($tableColumns)->map(fn ($col) => [
            'data' => $col['data'],
            'name' => $col['name'],
            'orderable' => $col['orderable'] ?? true,
            'searchable' => $col['searchable'] ?? true,
            'className' => $col['className'] ?? '',
        ])->values()->all();

        $years = LeaveBalance::select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        $leaveTypes = LeaveType::active()->orderBy('sort_order')->get();

        return view('leave-balances.index', compact('tableColumns', 'dtColumns', 'years', 'leaveTypes'));
    }

    protected function createViewData(): array
    {
        return [
            'years' => LeaveBalance::select('year')
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year'),
            'leaveTypes' => LeaveType::active()->orderBy('sort_order')->get(),
        ];
    }

    /**
     * Display the user's own leave balance summary.
     */
    public function mySummary(Request $request): View
    {
        $user = auth()->user();
        $year = $request->get('year', now()->year);

        $balances = $this->balanceService->getUserBalances($user->id, $year);

        $pendingRequests = $user->leaveRequests()
            ->with('leaveType')
            ->pending()
            ->where('year', $year)
            ->orderBy('created_at', 'desc')
            ->get();

        $approvedRequests = $user->leaveRequests()
            ->with('leaveType')
            ->approved()
            ->where('year', $year)
            ->orderBy('start_date', 'desc')
            ->get();

        $availableYears = $user->leaveBalances()
            ->select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        if (empty($availableYears)) {
            $availableYears = [now()->year];
        }

        return view('leave-balances.my-summary', compact(
            'balances',
            'pendingRequests',
            'approvedRequests',
            'year',
            'availableYears'
        ));
    }

    /**
     * Display a user's leave balance summary (for admins).
     */
    public function userSummary(Request $request): View
    {
        $userId = $request->get('user_id');
        $year = $request->get('year', now()->year);

        if (! $userId) {
            abort(404, 'User not specified.');
        }

        $user = \App\Models\User::findOrFail($userId);

        $balances = $this->balanceService->getUserBalances($userId, $year);

        $allRequests = $user->leaveRequests()
            ->with('leaveType', 'approvedBy', 'rejectedBy')
            ->where('year', $year)
            ->orderBy('created_at', 'desc')
            ->get();

        $availableYears = $user->leaveBalances()
            ->select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        if (empty($availableYears)) {
            $availableYears = [$year];
        }

        return view('leave-balances.user-summary', compact(
            'user',
            'balances',
            'allRequests',
            'year',
            'availableYears'
        ));
    }

    /**
     * Get balance data via API (for AJAX requests).
     */
    public function getBalance(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'year' => ['required', 'integer'],
        ]);

        // Authorization: users can view their own balance, or admins can view any
        if ($request->user_id != auth()->id() && ! auth()->user()->can('view any leave balances')) {
            abort(403, 'You do not have permission to view this balance.');
        }

        $balance = $this->balanceService->getOrCreateBalance(
            $request->user_id,
            $request->leave_type_id,
            $request->year
        );

        return response()->json([
            'total_entitlement' => $balance->total_entitlement,
            'taken_days' => $balance->taken_days,
            'remaining_days' => $balance->remaining_days,
            'pending_days' => $balance->pending_days,
            'usage_percentage' => $balance->usage_percentage,
        ]);
    }
}
