<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\StoreDesignationRequest;
use App\Http\Requests\UpdateDesignationRequest;
use App\Models\Designation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DesignationController extends BaseController
{
    public function __construct(DesignationDataTableController $dataTableController)
    {
        $this->model = Designation::class;
        $this->routePrefix = 'designations';
        $this->viewPrefix = 'designations';
        $this->resourceName = 'Designation';
        $this->dataTableController = $dataTableController;
    }

    protected function authorizeAction(string $ability, ?Model $record = null): void
    {
        if ($record) {
            $this->authorize($ability, $record);
        } else {
            $this->authorize($ability, Designation::class);
        }
    }

    public function show(int|string $record): View
    {
        $designation = $this->findRecord($record);
        $this->authorize('view', $designation);

        return view('designations.show', compact('designation'));
    }

    public function store(StoreDesignationRequest $request): RedirectResponse
    {
        $this->authorize('create', Designation::class);

        Designation::create($request->validated());

        return $this->successRedirect('created');
    }

    public function update(UpdateDesignationRequest $request, int|string $record): RedirectResponse
    {
        $designation = $this->findRecord($record);
        $this->authorize('update', $designation);

        $designation->update($request->validated());

        return $this->successRedirect('updated');
    }
}
