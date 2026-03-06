<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\ApproveLeaveRequestRequest;
use App\Http\Requests\RejectLeaveRequestRequest;
use App\Http\Requests\StoreLeaveRequestRequest;
use App\Models\LeaveType;
use App\Services\LeaveBalanceService;
use App\Services\LeaveCalculationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LeaveRequestController extends BaseController
{
    public function __construct(
        LeaveRequestDataTableController $dataTableController,
        private LeaveBalanceService $balanceService,
        private LeaveCalculationService $calculationService
    ) {
        $this->model = \App\Models\LeaveRequest::class;
        $this->routePrefix = 'leave-requests';
        $this->viewPrefix = 'leave-requests';
        $this->resourceName = 'Leave Request';
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

        $years = \App\Models\LeaveRequest::select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        return view('leave-requests.index', compact('tableColumns', 'dtColumns', 'years'));
    }

    protected function createViewData(): array
    {
        return [
            'leaveTypes' => LeaveType::active()->orderBy('sort_order')->get(),
        ];
    }

    /**
     * Display the user's own leave requests.
     */
    public function myRequests(Request $request): View|\Illuminate\Http\JsonResponse
    {
        if ($request->ajax()) {
            return $this->dataTableController->datatable($request);
        }

        $availableYears = auth()->user()->leaveRequests()
            ->select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        return view('leave-requests.my-requests', compact('availableYears'));
    }

    /**
     * Show a specific leave request (admin view).
     */
    public function show(int|string $record): View
    {
        $leaveRequest = $this->findRecord($record);
        $this->authorizeAction('view', $leaveRequest);
        $leaveRequest->load(['user', 'leaveType', 'approvedBy', 'rejectedBy']);

        return view('leave-requests.show', compact('leaveRequest'));
    }

    /**
     * Show user's own leave request details.
     */
    public function showMy(int|string $record): View
    {
        $leaveRequest = $this->findRecord($record);

        if ($leaveRequest->user_id !== auth()->id()) {
            abort(403);
        }

        $leaveRequest->load(['leaveType', 'approvedBy', 'rejectedBy']);

        return view('leave-requests.show-my', compact('leaveRequest'));
    }

    /**
     * Store a new leave request.
     */
    public function store(StoreLeaveRequestRequest $request): RedirectResponse
    {
        $this->authorizeAction('create');

        $leaveType = LeaveType::findOrFail($request->leave_type_id);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        // Get user's location for holiday calculation
        $user = auth()->user();
        $employee = $user->employee;

        // Calculate actual days excluding weekends and holidays
        $totalDays = $this->calculationService->calculateActualDays(
            $startDate,
            $endDate,
            $employee->country_id ?? null,
            $employee->city_id ?? null
        );

        // Check if user has sufficient balance
        $currentYear = now()->year;
        if ($startDate->year !== $endDate->year) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Leave requests cannot span multiple years.');
        }

        $leaveBalance = $this->balanceService->getOrCreateBalance($user->id, $leaveType->id, $currentYear);

        if (! $leaveBalance->hasSufficientBalance($totalDays)) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Insufficient leave balance. You have {$leaveBalance->remaining_days} days remaining.");
        }

        DB::beginTransaction();
        try {
            $leaveRequest = \App\Models\LeaveRequest::create([
                'user_id' => $user->id,
                'leave_type_id' => $leaveType->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'reason' => $request->reason,
                'total_days' => $totalDays,
                'status' => 'pending',
                'year' => $currentYear,
            ]);

            // Update pending days in balance
            $this->balanceService->updatePendingDays($leaveBalance);

            DB::commit();

            return to_route('my-leave-requests.index')
                ->with('status', 'Leave request submitted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while submitting your request. Please try again.');
        }
    }

    /**
     * Approve a leave request.
     */
    public function approve(ApproveLeaveRequestRequest $request, int|string $record): JsonResponse
    {
        $leaveRequest = $this->findRecord($record);
        $this->authorizeAction('approve', $leaveRequest);

        if (! $leaveRequest->isPending()) {
            return response()->json(['error' => 'Only pending requests can be approved.'], 422);
        }

        DB::beginTransaction();
        try {
            $leaveRequest->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'approval_note' => $request->approval_note,
            ]);

            // Update leave balance
            $leaveBalance = $this->balanceService->getOrCreateBalance(
                $leaveRequest->user_id,
                $leaveRequest->leave_type_id,
                $leaveRequest->year
            );

            $this->balanceService->approveRequest($leaveBalance, $leaveRequest->total_days);

            DB::commit();

            return response()->json([
                'message' => 'Leave request approved successfully.',
                'new_balance' => $leaveBalance->fresh()->remaining_days,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => 'An error occurred while approving the request.'], 500);
        }
    }

    /**
     * Reject a leave request.
     */
    public function reject(RejectLeaveRequestRequest $request, int|string $record): JsonResponse
    {
        $leaveRequest = $this->findRecord($record);
        $this->authorizeAction('reject', $leaveRequest);

        if (! $leaveRequest->isPending()) {
            return response()->json(['error' => 'Only pending requests can be rejected.'], 422);
        }

        DB::beginTransaction();
        try {
            $leaveRequest->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
                'rejected_by' => auth()->id(),
                'rejected_at' => now(),
            ]);

            // Update pending days in balance
            $leaveBalance = $this->balanceService->getOrCreateBalance(
                $leaveRequest->user_id,
                $leaveRequest->leave_type_id,
                $leaveRequest->year
            );

            $this->balanceService->updatePendingDays($leaveBalance);

            DB::commit();

            return response()->json(['message' => 'Leave request rejected successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => 'An error occurred while rejecting the request.'], 500);
        }
    }

    /**
     * Cancel a leave request.
     */
    public function cancel(Request $request, int|string $record): JsonResponse
    {
        $leaveRequest = $this->findRecord($record);
        $this->authorizeAction('cancel', $leaveRequest);

        if (! $leaveRequest->canBeCancelled()) {
            return response()->json(['error' => 'This request cannot be cancelled.'], 422);
        }

        DB::beginTransaction();
        try {
            $leaveRequest->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            // Update pending days in balance
            $leaveBalance = $this->balanceService->getOrCreateBalance(
                $leaveRequest->user_id,
                $leaveRequest->leave_type_id,
                $leaveRequest->year
            );

            $this->balanceService->updatePendingDays($leaveBalance);

            DB::commit();

            return response()->json(['message' => 'Leave request cancelled successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => 'An error occurred while cancelling the request.'], 500);
        }
    }
}
