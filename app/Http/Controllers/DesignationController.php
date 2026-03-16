<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\DesignationRequest;
use App\Models\Designation;
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

    protected function requestClass(): ?string
    {
        return DesignationRequest::class;
    }

    public function show(int|string $record): View
    {
        $designation = $this->findRecord($record);
        $this->authorizeAction('view', $designation);

        return view('designations.show', compact('designation'));
    }
}
