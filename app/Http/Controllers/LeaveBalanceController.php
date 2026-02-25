<?php

namespace App\Http\Controllers;

use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Services\LeaveBalanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class LeaveBalanceController extends Controller
{
    public function __construct(
        private LeaveBalanceService $balanceService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $query = LeaveBalance::with(['user', 'leaveType'])
                ->orderBy('year', 'desc')
                ->orderBy('user_id')
                ->orderBy('leave_type_id');

            // Filter by year if provided
            if ($request->has('year') && $request->year) {
                $query->where('year', $request->year);
            }

            // Filter by leave type if provided
            if ($request->has('leave_type_id') && $request->leave_type_id) {
                $query->where('leave_type_id', $request->leave_type_id);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('employee_name', function (LeaveBalance $balance) {
                    return $balance->user->name;
                })
                ->addColumn('leave_type', function (LeaveBalance $balance) {
                    return '<span class="badge bg-info">'.$balance->leaveType->code.'</span> '.$balance->leaveType->name;
                })
                ->addColumn('entitlement', function (LeaveBalance $balance) {
                    return $balance->total_entitlement.' day'.($balance->total_entitlement > 1 ? 's' : '');
                })
                ->addColumn('usage', function (LeaveBalance $balance) {
                    $percentage = $balance->usage_percentage;
                    $color = $percentage >= 80 ? 'bg-danger' : ($percentage >= 50 ? 'bg-warning' : 'bg-success');

                    return '
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar '.$color.'" role="progressbar"
                                style="width: '.$percentage.'%"
                                aria-valuenow="'.$percentage.'" aria-valuemin="0" aria-valuemax="100">
                                '.$balance->taken_days.' / '.$balance->total_entitlement.'
                            </div>
                        </div>
                        <small class="text-muted">'.$percentage.'% used</small>
                    ';
                })
                ->addColumn('pending', function (LeaveBalance $balance) {
                    if ($balance->pending_days > 0) {
                        return '<span class="badge bg-warning">'.$balance->pending_days.' pending</span>';
                    }

                    return '<span class="text-muted">—</span>';
                })
                ->addColumn('remaining', function (LeaveBalance $balance) {
                    $remaining = $balance->remaining_days;
                    $class = $remaining <= 2 ? 'text-danger' : ($remaining <= 5 ? 'text-warning' : 'text-success');

                    return '<strong class="'.$class.'">'.$remaining.'</strong> day'.($remaining != 1 ? 's' : '');
                })
                ->addColumn('action', function (LeaveBalance $balance) {
                    $summaryUrl = route('leave-balances.summary', [
                        'user_id' => $balance->user_id,
                        'year' => $balance->year,
                    ]);

                    return '
                        <a href="'.$summaryUrl.'" class="btn btn-sm btn-info" title="View Summary">
                            <i class="bi bi-file-text"></i> Summary
                        </a>
                    ';
                })
                ->rawColumns(['leave_type', 'usage', 'pending', 'remaining', 'action'])
                ->make(true);
        }

        $years = LeaveBalance::select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        $leaveTypes = LeaveType::active()->orderBy('sort_order')->get();

        return view('leave-balances.index', compact('years', 'leaveTypes'));
    }

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
