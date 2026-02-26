<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApproveLeaveRequestRequest;
use App\Http\Requests\RejectLeaveRequestRequest;
use App\Http\Requests\StoreLeaveRequestRequest;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Services\LeaveBalanceService;
use App\Services\LeaveCalculationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class LeaveRequestController extends Controller
{
    public function __construct(
        private LeaveBalanceService $balanceService,
        private LeaveCalculationService $calculationService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $query = LeaveRequest::with(['user', 'leaveType', 'approvedBy', 'rejectedBy'])
                ->orderBy('created_at', 'desc');

            // Filter by status if provided
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            // Filter by year if provided
            if ($request->has('year') && $request->year) {
                $query->where('year', $request->year);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('employee_name', function (LeaveRequest $leaveRequest) {
                    return $leaveRequest->user->name;
                })
                ->addColumn('leave_type', function (LeaveRequest $leaveRequest) {
                    return '<span class="badge bg-info">'.$leaveRequest->leaveType->code.'</span> '.$leaveRequest->leaveType->name;
                })
                ->addColumn('date_range', function (LeaveRequest $leaveRequest) {
                    return $leaveRequest->start_date->format('M d, Y').' - '.$leaveRequest->end_date->format('M d, Y');
                })
                ->addColumn('total_days', function (LeaveRequest $leaveRequest) {
                    return $leaveRequest->total_days.' day'.($leaveRequest->total_days > 1 ? 's' : '');
                })
                ->addColumn('status_badge', function (LeaveRequest $leaveRequest) {
                    return $leaveRequest->status_badge;
                })
                ->addColumn('action', function (LeaveRequest $leaveRequest) {
                    $showUrl = route('leave-requests.show', $leaveRequest);

                    $actions = '
                        <div class="btn-group btn-group-sm">
                            <a href="'.$showUrl.'" class="btn btn-info" title="View">
                                <i class="bi bi-eye"></i>
                            </a>';

                    // Approve button for pending requests
                    if ($leaveRequest->isPending() && auth()->user()->can('approve leave requests')) {
                        $actions .= '
                            <button type="button" class="btn btn-success btn-approve"
                                data-url="'.route('leave-requests.approve', $leaveRequest).'"
                                data-name="'.e($leaveRequest->user->name).'" title="Approve">
                                <i class="bi bi-check-lg"></i>
                            </button>';
                    }

                    // Reject button for pending requests
                    if ($leaveRequest->isPending() && auth()->user()->can('reject leave requests')) {
                        $actions .= '
                            <button type="button" class="btn btn-danger btn-reject"
                                data-url="'.route('leave-requests.reject', $leaveRequest).'"
                                data-name="'.e($leaveRequest->user->name).'" title="Reject">
                                <i class="bi bi-x-lg"></i>
                            </button>';
                    }

                    // Cancel button for own pending requests
                    if ($leaveRequest->canBeCancelled() && $leaveRequest->user_id === auth()->id()) {
                        $actions .= '
                            <button type="button" class="btn btn-warning btn-cancel"
                                data-url="'.route('leave-requests.cancel', $leaveRequest).'"
                                data-name="'.e($leaveRequest->leaveType->name).'" title="Cancel">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>';
                    }

                    $actions .= '</div>';

                    return $actions;
                })
                ->rawColumns(['leave_type', 'status_badge', 'action'])
                ->make(true);
        }

        $years = LeaveRequest::select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        return view('leave-requests.index', compact('years'));
    }

    public function myRequests(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $query = auth()->user()->leaveRequests()
                ->with(['leaveType', 'approvedBy', 'rejectedBy'])
                ->orderBy('created_at', 'desc');

            // Filter by status if provided
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            // Filter by year if provided
            if ($request->has('year') && $request->year) {
                $query->where('year', $request->year);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('leave_type', function (LeaveRequest $leaveRequest) {
                    return '<span class="badge bg-info">'.$leaveRequest->leaveType->code.'</span> '.$leaveRequest->leaveType->name;
                })
                ->addColumn('date_range', function (LeaveRequest $leaveRequest) {
                    return $leaveRequest->start_date->format('M d, Y').' - '.$leaveRequest->end_date->format('M d, Y');
                })
                ->addColumn('total_days', function (LeaveRequest $leaveRequest) {
                    return $leaveRequest->total_days.' day'.($leaveRequest->total_days > 1 ? 's' : '');
                })
                ->addColumn('status_badge', function (LeaveRequest $leaveRequest) {
                    return $leaveRequest->status_badge;
                })
                ->addColumn('action', function (LeaveRequest $leaveRequest) {
                    $showUrl = route('my-leave-requests.show', $leaveRequest);

                    $actions = '
                        <div class="btn-group btn-group-sm">
                            <a href="'.$showUrl.'" class="btn btn-info" title="View">
                                <i class="bi bi-eye"></i>
                            </a>';

                    // Cancel button for own pending requests
                    if ($leaveRequest->canBeCancelled()) {
                        $actions .= '
                            <button type="button" class="btn btn-warning btn-cancel"
                                data-url="'.route('my-leave-requests.cancel', $leaveRequest).'"
                                data-name="'.e($leaveRequest->leaveType->name).'" title="Cancel">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>';
                    }

                    $actions .= '</div>';

                    return $actions;
                })
                ->rawColumns(['leave_type', 'status_badge', 'action'])
                ->make(true);
        }

        $availableYears = auth()->user()->leaveRequests()
            ->select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        return view('leave-requests.my-requests', compact('availableYears'));
    }

    public function create(): View
    {
        $leaveTypes = LeaveType::active()->orderBy('sort_order')->get();

        return view('leave-requests.create', compact('leaveTypes'));
    }

    public function store(StoreLeaveRequestRequest $request): RedirectResponse
    {
        $leaveType = LeaveType::findOrFail($request->leave_type_id);

        $startDate = \Carbon\Carbon::parse($request->start_date);
        $endDate = \Carbon\Carbon::parse($request->end_date);

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
            $leaveRequest = LeaveRequest::create([
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

    public function show(LeaveRequest $leaveRequest): View
    {
        $leaveRequest->load(['user', 'leaveType', 'approvedBy', 'rejectedBy']);

        return view('leave-requests.show', compact('leaveRequest'));
    }

    public function showMy(LeaveRequest $leaveRequest): View
    {
        if ($leaveRequest->user_id !== auth()->id()) {
            abort(403);
        }

        $leaveRequest->load(['leaveType', 'approvedBy', 'rejectedBy']);

        return view('leave-requests.show-my', compact('leaveRequest'));
    }

    public function approve(ApproveLeaveRequestRequest $request, LeaveRequest $leaveRequest): JsonResponse
    {
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

    public function reject(RejectLeaveRequestRequest $request, LeaveRequest $leaveRequest): JsonResponse
    {
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

    public function cancel(Request $request, LeaveRequest $leaveRequest): JsonResponse
    {
        if ($leaveRequest->user_id !== auth()->id() && ! auth()->user()->can('cancel leave requests')) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

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
