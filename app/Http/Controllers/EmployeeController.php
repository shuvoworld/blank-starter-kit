<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Department;
use App\Models\Designation;
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
            $query = Employee::query()->with('departmentRelation', 'designation');

            $query->byDepartmentId($request->input('filter_department_id'));
            $query->byDesignationId($request->input('filter_designation_id'));
            $query->byStatus($request->input('filter_status'));
            $query->byHireDateRange(
                $request->input('filter_hire_date_from'),
                $request->input('filter_hire_date_to')
            );

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('profile_picture', function (Employee $employee) {
                    $url = $employee->getFirstMediaUrl('profile_picture', 'thumb');
                    if ($url) {
                        return '<img src="'.$url.'" alt="'.e($employee->name).'" class="rounded-circle" width="40" height="40" style="object-fit: cover;">';
                    }
                    return '<div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center text-white" style="width: 40px; height: 40px; font-size: 1.2rem;"><i class="bi bi-person-fill"></i></div>';
                })
                ->addColumn('department_name', function (Employee $employee) {
                    return $employee->departmentRelation?->name ?? $employee->department ?? '—';
                })
                ->addColumn('designation_name', function (Employee $employee) {
                    return $employee->designation?->name ?? $employee->position ?? '—';
                })
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
                ->rawColumns(['profile_picture', 'status_badge', 'action'])
                ->make(true);
        }

        $departments = Department::active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);

        $designations = Designation::active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('employees.index', compact('departments', 'designations'));
    }

    public function create(): View
    {
        $departments = Department::active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);

        $designations = Designation::active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('employees.create', compact('departments', 'designations'));
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $employee = Employee::create($request->validated());

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            $employee->addMediaFromRequest('profile_picture')
                ->toMediaCollection('profile_picture');
        }

        // Handle resume upload
        if ($request->hasFile('resume')) {
            $employee->addMediaFromRequest('resume')
                ->toMediaCollection('resume');
        }

        // Handle certificates upload (multiple files)
        if ($request->hasFile('certificates')) {
            foreach ($request->file('certificates') as $certificate) {
                $employee->addMedia($certificate)
                    ->toMediaCollection('certificates');
            }
        }

        // Handle documents upload (multiple files)
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $employee->addMedia($document)
                    ->toMediaCollection('documents');
            }
        }

        return to_route('employees.index')->with('status', 'Employee created successfully.');
    }

    public function show(Employee $employee): View
    {
        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee): View
    {
        $departments = Department::active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);

        $designations = Designation::active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('employees.edit', compact('employee', 'departments', 'designations'));
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $employee->update($request->validated());

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Clear existing profile picture
            $employee->clearMediaCollection('profile_picture');

            $employee->addMediaFromRequest('profile_picture')
                ->toMediaCollection('profile_picture');
        }

        // Handle resume upload
        if ($request->hasFile('resume')) {
            // Clear existing resume
            $employee->clearMediaCollection('resume');

            $employee->addMediaFromRequest('resume')
                ->toMediaCollection('resume');
        }

        // Handle certificates upload (multiple files)
        if ($request->hasFile('certificates')) {
            foreach ($request->file('certificates') as $certificate) {
                $employee->addMedia($certificate)
                    ->toMediaCollection('certificates');
            }
        }

        // Handle documents upload (multiple files)
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $employee->addMedia($document)
                    ->toMediaCollection('documents');
            }
        }

        return to_route('employees.index')->with('status', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee): JsonResponse
    {
        $employee->delete();

        return response()->json(['message' => 'Employee deleted successfully.']);
    }
}
