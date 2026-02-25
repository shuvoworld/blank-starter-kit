<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeaveTypeRequest;
use App\Http\Requests\UpdateLeaveTypeRequest;
use App\Models\LeaveType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class LeaveTypeController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $query = LeaveType::query()->orderBy('sort_order');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('code_badge', function (LeaveType $leaveType) {
                    return '<span class="badge bg-info">'.$leaveType->code.'</span>';
                })
                ->addColumn('is_paid_badge', function (LeaveType $leaveType) {
                    $class = $leaveType->is_paid ? 'bg-success' : 'bg-warning';

                    return '<span class="badge '.$class.'">'.($leaveType->is_paid ? 'Paid' : 'Unpaid').'</span>';
                })
                ->addColumn('max_days_per_year', function (LeaveType $leaveType) {
                    return $leaveType->max_days_per_year ?? '—';
                })
                ->addColumn('carry_forward_badge', function (LeaveType $leaveType) {
                    if ($leaveType->carry_forward) {
                        $limit = $leaveType->carry_forward_limit ? " (max {$leaveType->carry_forward_limit})" : '';

                        return '<span class="badge bg-success">Yes'.$limit.'</span>';
                    }

                    return '<span class="badge bg-secondary">No</span>';
                })
                ->addColumn('status_badge', function (LeaveType $leaveType) {
                    $class = $leaveType->is_active ? 'bg-success' : 'bg-secondary';

                    return '<span class="badge '.$class.'">'.($leaveType->is_active ? 'Active' : 'Inactive').'</span>';
                })
                ->addColumn('action', function (LeaveType $leaveType) {
                    $showUrl = route('leave-types.show', $leaveType);
                    $editUrl = route('leave-types.edit', $leaveType);
                    $deleteUrl = route('leave-types.destroy', $leaveType);

                    return '
                        <div class="btn-group btn-group-sm">
                            <a href="'.$showUrl.'" class="btn btn-info" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="'.$editUrl.'" class="btn btn-primary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button type="button" class="btn btn-danger btn-delete"
                                data-url="'.$deleteUrl.'"
                                data-name="'.e($leaveType->name).'" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    ';
                })
                ->rawColumns(['code_badge', 'is_paid_badge', 'carry_forward_badge', 'status_badge', 'action'])
                ->make(true);
        }

        return view('leave-types.index');
    }

    public function create(): View
    {
        return view('leave-types.create');
    }

    public function store(StoreLeaveTypeRequest $request): RedirectResponse
    {
        LeaveType::create($request->validated());

        return to_route('leave-types.index')->with('status', 'Leave type created successfully.');
    }

    public function show(LeaveType $leaveType): View
    {
        return view('leave-types.show', compact('leaveType'));
    }

    public function edit(LeaveType $leaveType): View
    {
        return view('leave-types.edit', compact('leaveType'));
    }

    public function update(UpdateLeaveTypeRequest $request, LeaveType $leaveType): RedirectResponse
    {
        $leaveType->update($request->validated());

        return to_route('leave-types.index')->with('status', 'Leave type updated successfully.');
    }

    public function destroy(LeaveType $leaveType): JsonResponse
    {
        $leaveType->delete();

        return response()->json(['message' => 'Leave type deleted successfully.']);
    }
}
