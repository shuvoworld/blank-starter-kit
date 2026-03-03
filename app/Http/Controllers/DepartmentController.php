<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class DepartmentController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        // Verify the current user can view the department list before loading any data.
        $this->authorize('viewAny', Department::class);

        if ($request->ajax()) {
            $query = Department::query()->orderBy('sort_order');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('status_badge', function (Department $department) {
                    $class = $department->is_active ? 'bg-success' : 'bg-secondary';

                    return '<span class="badge '.$class.'">'.($department->is_active ? 'Active' : 'Inactive').'</span>';
                })
                ->addColumn('action', function (Department $department) {
                    $showUrl = route('departments.show', $department);
                    $editUrl = route('departments.edit', $department);
                    $deleteUrl = route('departments.destroy', $department);

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
                                data-name="'.e($department->name).'" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    ';
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('departments.index');
    }

    public function create(): View
    {
        // Verify the current user can create departments before showing the form.
        $this->authorize('create', Department::class);

        return view('departments.create');
    }

    public function store(StoreDepartmentRequest $request): RedirectResponse
    {
        // Re-check on form submit — a user should not be able to bypass
        // the form by posting directly to this endpoint without permission.
        $this->authorize('create', Department::class);

        Department::create($request->validated());

        return to_route('departments.index')->with('status', 'Department created successfully.');
    }

    public function show(Department $department): View
    {
        // Verify the current user can view this specific department record.
        $this->authorize('view', $department);

        return view('departments.show', compact('department'));
    }

    public function edit(Department $department): View
    {
        // Verify the current user can edit this specific department record.
        $this->authorize('update', $department);

        return view('departments.edit', compact('department'));
    }

    public function update(UpdateDepartmentRequest $request, Department $department): RedirectResponse
    {
        // Re-check on form submit — same reason as store() above.
        $this->authorize('update', $department);

        $department->update($request->validated());

        return to_route('departments.index')->with('status', 'Department updated successfully.');
    }

    public function destroy(Department $department): JsonResponse
    {
        // Verify the current user can delete this specific department record.
        $this->authorize('delete', $department);

        $department->delete();

        return response()->json(['message' => 'Department deleted successfully.']);
    }
}
