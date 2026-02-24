<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLandingPageSectionRequest;
use App\Http\Requests\UpdateLandingPageSectionRequest;
use App\Models\LandingPageSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class LandingPageSectionController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $query = LandingPageSection::query();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('section_badge', function (LandingPageSection $section) {
                    $colors = [
                        'general' => 'primary',
                        'hero' => 'success',
                        'features' => 'info',
                        'stats' => 'warning',
                        'about' => 'danger',
                        'pricing' => 'secondary',
                        'cta' => 'dark',
                        'contact' => 'purple',
                        'footer' => 'light',
                    ];

                    $color = $colors[$section->section] ?? 'secondary';

                    return '<span class="badge bg-' . $color . '">' . ucfirst($section->section) . '</span>';
                })
                ->addColumn('type_badge', function (LandingPageSection $section) {
                    $colors = [
                        'text' => 'primary',
                        'textarea' => 'info',
                        'image' => 'warning',
                        'boolean' => 'success',
                        'json' => 'danger',
                        'number' => 'secondary',
                    ];

                    $color = $colors[$section->type] ?? 'secondary';

                    return '<span class="badge bg-' . $color . '">' . ucfirst($section->type) . '</span>';
                })
                ->addColumn('status_badge', function (LandingPageSection $section) {
                    if ($section->is_active) {
                        return '<span class="badge bg-success">Active</span>';
                    }

                    return '<span class="badge bg-secondary">Inactive</span>';
                })
                ->addColumn('preview', function (LandingPageSection $section) {
                    $value = strlen($section->value) > 50
                        ? substr($section->value, 0, 50) . '...'
                        : $section->value;

                    return '<span class="text-muted small">' . e($value ?: 'Empty') . '</span>';
                })
                ->addColumn('action', function (LandingPageSection $section) {
                    $editUrl = route('landing-page-sections.edit', $section);

                    return '
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-primary btn-edit-section" data-url="' . $editUrl . '" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                        </div>
                    ';
                })
                ->rawColumns(['section_badge', 'type_badge', 'status_badge', 'preview', 'action'])
                ->make(true);
        }

        $sections = LandingPageSection::orderBy('section')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('section');

        return view('landing-page-sections.index', compact('sections'));
    }

    public function edit(LandingPageSection $landingPageSection): View|JsonResponse
    {
        if (\request()->ajax() || \request()->wantsJson()) {
            return response()->json($landingPageSection);
        }

        return view('landing-page-sections.edit', compact('landingPageSection'));
    }

    public function update(UpdateLandingPageSectionRequest $request, LandingPageSection $landingPageSection): RedirectResponse
    {
        $landingPageSection->update($request->validated());

        return to_route('landing-page-sections.index')
            ->with('status', 'Landing page section updated successfully.');
    }
}
