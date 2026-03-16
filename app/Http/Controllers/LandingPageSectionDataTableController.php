<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController\BaseDataTableController;
use App\Models\LandingPageSection;

class LandingPageSectionDataTableController extends BaseDataTableController
{
    public function __construct()
    {
        $this->model = LandingPageSection::class;
        $this->routePrefix = 'landing-page-sections';
        $this->rawColumns = ['section_badge', 'type_badge', 'status_badge', 'preview', 'updated_at', 'updated_by'];
    }

    protected function indexQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return LandingPageSection::query()->with('updatedBy')->orderBy('section')->orderBy('sort_order');
    }

    protected function dataTableColumns(): array
    {
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

        $typeColors = [
            'text' => 'primary',
            'textarea' => 'info',
            'image' => 'warning',
            'boolean' => 'success',
            'json' => 'danger',
            'number' => 'secondary',
        ];

        return [
            'section_badge' => function (LandingPageSection $section) use ($colors) {
                $color = $colors[$section->section] ?? 'secondary';

                return '<span class="badge bg-'.$color.'">'.ucfirst($section->section).'</span>';
            },

            'type_badge' => function (LandingPageSection $section) use ($typeColors) {
                $color = $typeColors[$section->type] ?? 'secondary';

                return '<span class="badge bg-'.$color.'">'.ucfirst($section->type).'</span>';
            },

            'status_badge' => fn (LandingPageSection $section) => $section->is_active
                ? '<span class="badge bg-success">Active</span>'
                : '<span class="badge bg-secondary">Inactive</span>',

            'preview' => fn (LandingPageSection $section) => '<span class="text-muted small">'
                .e(strlen($section->value) > 50 ? substr($section->value, 0, 50).'...' : ($section->value ?: 'Empty'))
                .'</span>',

            'updated_at' => fn (LandingPageSection $section) => $section->updated_at->format('M d, Y H:i'),

            'updated_by' => fn (LandingPageSection $section) => $section->updatedBy?->name ?? '—',
        ];
    }

    protected function actionColumn($section): string
    {
        $editUrl = route('landing-page-sections.edit', $section);

        return '
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-primary btn-edit-section" data-url="'.$editUrl.'" title="Edit">
                    <i class="bi bi-pencil"></i>
                </button>
            </div>
        ';
    }

    public function tableColumns(): array
    {
        return [
            [
                'data' => 'id',
                'name' => 'id',
                'label' => 'ID',
                'width' => '70',
                'className' => 'text-center',
            ],
            [
                'data' => 'section_badge',
                'name' => 'section',
                'label' => 'Section',
            ],
            [
                'data' => 'key',
                'name' => 'key',
                'label' => 'Key',
            ],
            [
                'data' => 'status_badge',
                'name' => 'is_active',
                'label' => 'Status',
                'orderable' => false,
                'searchable' => false,
            ],
            [
                'data' => 'updated_at',
                'name' => 'updated_at',
                'label' => 'Updated At',
                'className' => 'text-nowrap',
            ],
            [
                'data' => 'updated_by',
                'name' => 'updated_by',
                'label' => 'Updated By',
                'orderable' => false,
                'searchable' => false,
            ],
            [
                'data' => 'action',
                'name' => 'action',
                'label' => 'Actions',
                'width' => '80',
                'orderable' => false,
                'searchable' => false,
                'className' => 'text-center',
            ],
        ];
    }
}
