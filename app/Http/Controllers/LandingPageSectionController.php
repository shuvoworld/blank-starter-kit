<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\LandingPageSectionRequest;
use App\Models\LandingPageSection;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class LandingPageSectionController extends BaseController
{
    public function __construct(LandingPageSectionDataTableController $dataTableController)
    {
        $this->model = LandingPageSection::class;
        $this->routePrefix = 'landing-page-sections';
        $this->viewPrefix = 'landing-page-sections';
        $this->resourceName = 'Landing Page Section';
        $this->dataTableController = $dataTableController;
    }

    protected function requestClass(): ?string
    {
        return LandingPageSectionRequest::class;
    }

    /**
     * Edit method returns JSON for AJAX requests or view for regular requests.
     */
    public function edit(int|string $record): View|JsonResponse
    {
        $section = $this->findRecord($record);
        $this->authorizeAction('update', $section);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json($section);
        }

        return view('landing-page-sections.edit', compact('section'));
    }
}
