<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDesignationRequest;
use App\Http\Requests\UpdateDesignationRequest;
use App\Models\Designation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class DesignationController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        // Verify the current user can view the designation list before loading any data.
        $this->authorize('viewAny', Designation::class);

        if ($request->ajax()) {
            $query = Designation::query();

            $query->byStatus($request->input('filter_status'));

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('status_badge', function (Designation $designation) {
                    $class = $designation->is_active ? 'bg-success' : 'bg-secondary';

                    return '<span class="badge '.$class.'">'.($designation->is_active ? 'Active' : 'Inactive').'</span>';
                })
                ->addColumn('action', function (Designation $designation) {
                    $showUrl = route('designations.show', $designation);
                    $editUrl = route('designations.edit', $designation);
                    $deleteUrl = route('designations.destroy', $designation);

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
                                data-name="'.e($designation->name).'" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    ';
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('designations.index');
    }

    public function create(): View
    {
        // Verify the current user can create designations before showing the form.
        $this->authorize('create', Designation::class);

        return view('designations.create');
    }

    public function store(StoreDesignationRequest $request): RedirectResponse
    {
        // Re-check on form submit to prevent direct POST requests bypassing the form.
        $this->authorize('create', Designation::class);

        Designation::create($request->validated());

        return to_route('designations.index')->with('status', 'Designation created successfully.');
    }

    public function show(Designation $designation): View
    {
        // Verify the current user can view this specific designation record.
        $this->authorize('view', $designation);

        return view('designations.show', compact('designation'));
    }

    public function edit(Designation $designation): View
    {
        // Verify the current user can edit this specific designation record.
        $this->authorize('update', $designation);

        return view('designations.edit', compact('designation'));
    }

    public function update(UpdateDesignationRequest $request, Designation $designation): RedirectResponse
    {
        // Re-check on form submit to prevent direct PUT requests bypassing the form.
        $this->authorize('update', $designation);

        $designation->update($request->validated());

        return to_route('designations.index')->with('status', 'Designation updated successfully.');
    }

    public function destroy(Designation $designation): JsonResponse
    {
        // Verify the current user can delete this specific designation record.
        $this->authorize('delete', $designation);

        $designation->delete();

        return response()->json(['message' => 'Designation deleted successfully.']);
    }
}
