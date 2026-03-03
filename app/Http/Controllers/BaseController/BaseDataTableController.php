<?php

namespace App\Http\Controllers\BaseController;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

abstract class BaseDataTableController extends Controller
{
    protected string $model;
    protected string $routePrefix;
    protected array $withRelations = [];

    /**
     * Raw HTML columns defined by child. Merged with base defaults — never override base.
     */
    protected array $rawColumns = [];

    /**
     * Columns that are always raw regardless of child config.
     */
    private array $baseRawColumns = ['action'];

    /**
     * Override to gate the datatable endpoint.
     * Example: $this->authorize('viewAny', User::class);
     */
    protected function authorizeDataTable(): void {}

    /**
     * Base query. Override to add ordering, scopes, etc.
     */
    protected function indexQuery(): Builder
    {
        return $this->model::query()->with($this->withRelations);
    }

    /**
     * Custom columns: ['columnName' => fn($record) => string]
     */
    protected function dataTableColumns(): array
    {
        return [];
    }

    /**
     * Override to apply additional filters (search, date range, status, etc.)
     * $dt is the DataTables instance, $request has query params.
     */
    protected function applyFilters(\Yajra\DataTables\EloquentDataTable $dt, Request $request): \Yajra\DataTables\EloquentDataTable
    {
        return $dt;
    }
    public function tableColumns(): array
    {
        return [
            [
                'data'       => 'DT_RowIndex',
                'name'       => 'DT_RowIndex',
                'label'      => '#',
                'width'      => '50',
                'orderable'  => false,
                'searchable' => false,
                'className'  => 'text-center',
            ],
            [
                'data'  => 'name',
                'name'  => 'name',
                'label' => 'Name',
            ],
            [
                'data'       => 'is_active',
                'name'       => 'is_active',
                'label'      => 'Status',
                'orderable'  => true,
                'searchable' => false,
                'className'  => 'text-center',
            ],
            [
                'data'       => 'action',
                'name'       => 'action',
                'label'      => 'Actions',
                'width'      => '180',
                'orderable'  => false,
                'searchable' => false,
                'className'  => 'text-center',
            ],
        ];
    }
    /**
     * Action buttons column. Override for modules that need view/extra buttons.
     */
    protected function actionColumn($record): string
    {
        $editUrl   = route("{$this->routePrefix}.edit", $record);
        $deleteUrl = route("{$this->routePrefix}.destroy", $record);

        return '
            <div class="btn-group btn-group-sm">
                <a href="' . $editUrl . '" class="btn btn-primary" title="Edit">
                    <i class="bi bi-pencil"></i>
                </a>
                <button type="button" class="btn btn-danger btn-delete"
                    data-url="' . $deleteUrl . '"
                    data-name="' . e($this->getRecordName($record)) . '" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        ';
    }

    protected function getRecordName($record): string
    {
        return $record->name ?? $record->title ?? (string) $record->id;
    }

    public function datatable(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 403);

        $this->authorizeDataTable();

        /** @var \Yajra\DataTables\EloquentDataTable $dt */
        $dt = DataTables::of($this->indexQuery())->addIndexColumn();

        foreach ($this->dataTableColumns() as $column => $callback) {
            $dt->addColumn($column, $callback);
        }

        $dt->addColumn('action', fn ($record) => $this->actionColumn($record));

        $dt = $this->applyFilters($dt, $request);

        // Merge child rawColumns with base — child never accidentally loses 'action'
        $dt->rawColumns(array_unique(array_merge($this->baseRawColumns, $this->rawColumns)));

        return $dt->make(true);
    }
}
