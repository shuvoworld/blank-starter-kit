<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class EmployeeController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $query = Employee::query();

            $query->byDepartment($request->input('filter_department'));
            $query->byPosition($request->input('filter_position'));
            $query->byStatus($request->input('filter_status'));
            $query->byHireDateRange(
                $request->input('filter_hire_date_from'),
                $request->input('filter_hire_date_to')
            );

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('status_badge', function (Employee $employee) {
                    $class = $employee->status === 'active' ? 'bg-success' : 'bg-secondary';

                    return '<span class="badge '.$class.'">'.ucfirst($employee->status).'</span>';
                })
                ->addColumn('action', function (Employee $employee) {
                    $showUrl = route('employees.show', $employee);
                    $editUrl = route('employees.edit', $employee);
                    $deleteUrl = route('employees.destroy', $employee);

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
                                data-name="'.e($employee->name).'" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    ';
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        $departments = Employee::query()
            ->select('department')
            ->distinct()
            ->whereNotNull('department')
            ->pluck('department')
            ->sort()
            ->values();

        $positions = Employee::query()
            ->select('position')
            ->distinct()
            ->whereNotNull('position')
            ->pluck('position')
            ->sort()
            ->values();

        return view('employees.index', compact('departments', 'positions'));
    }

    public function create(): View
    {
        return view('employees.create');
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        Employee::create($request->validated());

        return to_route('employees.index')->with('status', 'Employee created successfully.');
    }

    public function show(Employee $employee): View
    {
        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee): View
    {
        return view('employees.edit', compact('employee'));
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $employee->update($request->validated());

        return to_route('employees.index')->with('status', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee): JsonResponse
    {
        $employee->delete();

        return response()->json(['message' => 'Employee deleted successfully.']);
    }
}
