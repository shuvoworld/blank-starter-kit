<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Services\LeaveBalanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Milenmk\LaravelLocations\Models\Area;
use Milenmk\LaravelLocations\Models\City;
use Milenmk\LaravelLocations\Models\Country;
use Yajra\DataTables\Facades\DataTables;

class EmployeeController extends Controller
{
    public function __construct(
        private LeaveBalanceService $balanceService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $query = Employee::query()->with('user', 'departmentRelation', 'designation', 'country', 'city', 'area');

            $query->byDepartmentId($request->input('filter_department_id'));
            $query->byDesignationId($request->input('filter_designation_id'));
            $query->byCountry($request->input('filter_country_id'));
            $query->byCity($request->input('filter_city_id'));
            $query->byArea($request->input('filter_area_id'));
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
                ->addColumn('user_badge', function (Employee $employee) {
                    if ($employee->user) {
                        return '<span class="badge bg-primary"><i class="bi bi-person-check me-1"></i>'.e($employee->user->name).'</span>';
                    }

                    return '<span class="text-muted">—</span>';
                })
                ->addColumn('department_name', function (Employee $employee) {
                    return $employee->departmentRelation?->name ?? $employee->department ?? '—';
                })
                ->addColumn('designation_name', function (Employee $employee) {
                    return $employee->designation?->name ?? $employee->position ?? '—';
                })
                ->addColumn('location', function (Employee $employee) {
                    $parts = array_filter([
                        $employee->area?->name,
                        $employee->city?->name,
                        $employee->country?->name,
                    ]);

                    return implode(', ', $parts) ?: '—';
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
                ->rawColumns(['profile_picture', 'user_badge', 'status_badge', 'action'])
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

        $countries = Country::activated()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('employees.index', compact('departments', 'designations', 'countries'));
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

        $countries = Country::activated()
            ->orderBy('name')
            ->get(['id', 'name']);

        $users = \App\Models\User::orderBy('name')->get(['id', 'name', 'email']);

        return view('employees.create', compact('departments', 'designations', 'countries', 'users'));
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
        $employee->load('user', 'country', 'city', 'area');

        // Get leave summary if employee has a user
        $leaveSummary = null;
        $currentYear = now()->year;
        if ($employee->user) {
            $leaveSummary = $this->balanceService->getLeaveSummary($employee->user->id, $currentYear);
        }

        return view('employees.show', compact('employee', 'leaveSummary', 'currentYear'));
    }

    public function edit(Employee $employee): View
    {
        $employee->load('user', 'country', 'city', 'area');

        $departments = Department::active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);

        $designations = Designation::active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);

        $countries = Country::activated()
            ->orderBy('name')
            ->get(['id', 'name']);

        $cities = $employee->country_id
            ? City::activated()->where('country_id', $employee->country_id)->get(['id', 'name'])
            : collect();

        $areas = $employee->city_id
            ? Area::activated()->where('city_id', $employee->city_id)->get(['id', 'name'])
            : collect();

        $users = \App\Models\User::orderBy('name')->get(['id', 'name', 'email']);

        return view('employees.edit', compact('employee', 'departments', 'designations', 'countries', 'cities', 'areas', 'users'));
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

    /**
     * Get cities by country for dynamic dropdown.
     */
    public function citiesByCountry(Request $request): JsonResponse
    {
        $request->validate([
            'country_id' => ['required', 'exists:countries,id'],
        ]);

        $cities = City::activated()
            ->where('country_id', $request->input('country_id'))
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($cities);
    }

    /**
     * Get areas by city for dynamic dropdown.
     */
    public function areasByCity(Request $request): JsonResponse
    {
        $request->validate([
            'city_id' => ['required', 'exists:cities,id'],
        ]);

        $areas = Area::activated()
            ->where('city_id', $request->input('city_id'))
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($areas);
    }
}
