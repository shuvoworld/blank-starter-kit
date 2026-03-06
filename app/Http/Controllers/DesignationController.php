<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\StoreDesignationRequest;
use App\Http\Requests\UpdateDesignationRequest;
use App\Models\Designation;
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

    public function show(int|string $record): View
    {
        $designation = $this->findRecord($record);
        $this->authorizeAction('view', $designation);

        return view('designations.show', compact('designation'));
    }

    public function store(StoreDesignationRequest $request): RedirectResponse
    {
        $this->authorizeAction('create');

        Designation::create($request->validated());

        return $this->successRedirect('created');
    }

    public function update(UpdateDesignationRequest $request, int|string $record): RedirectResponse
    {
        $designation = $this->findRecord($record);
        $this->authorizeAction('update', $designation);

        $designation->update($request->validated());

        return $this->successRedirect('updated');
    }
}
