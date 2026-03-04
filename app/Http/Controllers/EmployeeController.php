<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\User;
use App\Services\LeaveBalanceService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Milenmk\LaravelLocations\Models\Area;
use Milenmk\LaravelLocations\Models\City;
use Milenmk\LaravelLocations\Models\Country;

class EmployeeController extends BaseController
{
    public function __construct(
        EmployeeDataTableController $dataTableController,
        private readonly LeaveBalanceService $balanceService,
    ) {
        $this->model = Employee::class;
        $this->routePrefix = 'employees';
        $this->viewPrefix = 'employees';
        $this->resourceName = 'Employee';
        $this->dataTableController = $dataTableController;
    }

    protected function authorizeAction(string $ability, ?Model $record = null): void
    {
        if ($record) {
            $this->authorize($ability, $record);
        } else {
            $this->authorize($ability, Employee::class);
        }
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

        $departments = Department::active()->orderBy('sort_order')->orderBy('name')->get(['id', 'name']);
        $designations = Designation::active()->orderBy('sort_order')->orderBy('name')->get(['id', 'name']);
        $countries = Country::activated()->orderBy('name')->get(['id', 'name']);

        return view('employees.index', compact('tableColumns', 'dtColumns', 'departments', 'designations', 'countries'));
    }

    protected function createViewData(): array
    {
        return [
            'departments' => Department::active()->orderBy('sort_order')->orderBy('name')->get(['id', 'name']),
            'designations' => Designation::active()->orderBy('sort_order')->orderBy('name')->get(['id', 'name']),
            'countries' => Country::activated()->orderBy('name')->get(['id', 'name']),
            'cities' => collect(),
            'areas' => collect(),
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
        ];
    }

    protected function editViewData(Model $record): array
    {
        $record->load('user', 'country', 'city', 'area');

        return [
            'departments' => Department::active()->orderBy('sort_order')->orderBy('name')->get(['id', 'name']),
            'designations' => Designation::active()->orderBy('sort_order')->orderBy('name')->get(['id', 'name']),
            'countries' => Country::activated()->orderBy('name')->get(['id', 'name']),
            'cities' => $record->country_id
                ? City::activated()->where('country_id', $record->country_id)->get(['id', 'name'])
                : collect(),
            'areas' => $record->city_id
                ? Area::activated()->where('city_id', $record->city_id)->get(['id', 'name'])
                : collect(),
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
        ];
    }

    public function show(int|string $record): View
    {
        $employee = $this->findRecord($record);
        $this->authorize('view', $employee);

        $employee->load('user', 'country', 'city', 'area', 'departmentRelation', 'designation');

        $leaveSummary = null;
        $currentYear = now()->year;
        if ($employee->user) {
            $leaveSummary = $this->balanceService->getLeaveSummary($employee->user->id, $currentYear);
        }

        return view('employees.show', compact('employee', 'leaveSummary', 'currentYear'));
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $this->authorize('create', Employee::class);

        $employee = Employee::create($request->validated());

        if ($request->hasFile('profile_picture')) {
            $employee->addMediaFromRequest('profile_picture')->toMediaCollection('profile_picture');
        }

        if ($request->hasFile('resume')) {
            $employee->addMediaFromRequest('resume')->toMediaCollection('resume');
        }

        if ($request->hasFile('certificates')) {
            foreach ($request->file('certificates') as $certificate) {
                $employee->addMedia($certificate)->toMediaCollection('certificates');
            }
        }

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $employee->addMedia($document)->toMediaCollection('documents');
            }
        }

        return $this->successRedirect('created');
    }

    public function update(UpdateEmployeeRequest $request, int|string $record): RedirectResponse
    {
        $employee = $this->findRecord($record);
        $this->authorize('update', $employee);

        $employee->update($request->validated());

        if ($request->hasFile('profile_picture')) {
            $employee->clearMediaCollection('profile_picture');
            $employee->addMediaFromRequest('profile_picture')->toMediaCollection('profile_picture');
        }

        if ($request->hasFile('resume')) {
            $employee->clearMediaCollection('resume');
            $employee->addMediaFromRequest('resume')->toMediaCollection('resume');
        }

        if ($request->hasFile('certificates')) {
            foreach ($request->file('certificates') as $certificate) {
                $employee->addMedia($certificate)->toMediaCollection('certificates');
            }
        }

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $employee->addMedia($document)->toMediaCollection('documents');
            }
        }

        return $this->successRedirect('updated');
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
