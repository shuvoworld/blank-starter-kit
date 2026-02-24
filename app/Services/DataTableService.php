<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Yajra\DataTables\Facades\DataTables;

class DataTableService
{
    /**
     * Make a DataTable response with common configuration.
     *
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public static function make(Builder $query, array $columns)
    {
        $dataTable = DataTables::of($query);

        // Add index column
        if (in_array('index', $columns)) {
            $dataTable->addIndexColumn();
        }

        // Process column definitions
        foreach ($columns as $column => $definition) {
            if ($column === 'index') {
                continue;
            }

            if (isset($definition['render'])) {
                $dataTable->editColumn($column, $definition['render']);
            }

            if (isset($definition['raw'])) {
                $dataTable->rawColumns($definition['raw']);
            }
        }

        return $dataTable->make(true);
    }

    /**
     * Get common DataTable language configuration.
     */
    public static function language(string $entityName = 'records'): array
    {
        return [
            'processing' => '<div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>',
            'search' => '_INPUT_',
            'searchPlaceholder' => 'Search...',
            'lengthMenu' => 'Show _MENU_',
            'info' => "Showing _START_ to _END_ of _TOTAL_ {$entityName}",
            'infoEmpty' => "No {$entityName} found",
            'infoFiltered' => '(filtered from _MAX_ total)',
            'paginate' => [
                'first' => '<i class="bi bi-chevron-double-left"></i>',
                'previous' => '<i class="bi bi-chevron-left"></i>',
                'next' => '<i class="bi bi-chevron-right"></i>',
                'last' => '<i class="bi bi-chevron-double-right"></i>',
            ],
            'emptyTable' => '<div class="text-center py-4"><i class="bi bi-table fs-1 text-muted"></i><p class="text-muted mt-2">No '.$entityName.' found</p></div>',
        ];
    }

    /**
     * Get common DataTable DOM structure.
     */
    public static function dom(): string
    {
        return "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>".
               "<'row'<'col-sm-12'tr>>".
               "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>";
    }

    /**
     * Common badge renderers.
     */
    public static function badge(string $color = 'primary'): string
    {
        return '<span class="badge bg-'.$color.'">'.$color.'</span>';
    }

    /**
     * Render a badge for data.
     */
    public static function renderBadge(string $data, string $color = 'primary'): string
    {
        return '<span class="badge bg-'.$color.'">'.e($data).'</span>';
    }

    /**
     * Render action buttons.
     */
    public static function renderActions(array $actions, bool $dropdown = false): string
    {
        if ($dropdown && count($actions) > 2) {
            return self::renderDropdownActions($actions);
        }

        $html = '<div class="btn-group btn-group-sm">';

        foreach ($actions as $action) {
            $html .= self::renderActionButton($action);
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Render a single action button.
     */
    protected static function renderActionButton(array $action): string
    {
        $type = $action['type'] ?? 'link';
        $icon = $action['icon'] ?? 'bi-circle';
        $title = $action['title'] ?? '';
        $color = $action['color'] ?? 'primary';

        return match ($type) {
            'link' => '<a href="'.$action['url'].'" class="btn btn-'.$color.'" title="'.$title.'"><i class="bi '.$icon.'"></i></a>',
            'button' => '<button type="button" class="btn btn-'.$color.'" title="'.$title.'" data-url="'.$action['url'].'" '.($action['data'] ?? '').'><i class="bi '.$icon.'"></i></button>',
            'form' => '<form action="'.$action['url'].'" method="POST" class="d-inline">'.csrf_field().method_field($action['method'] ?? 'DELETE').'<button type="submit" class="btn btn-'.$color.'" title="'.$title.'"><i class="bi '.$icon.'"></i></button></form>',
            default => '',
        };
    }

    /**
     * Render dropdown action buttons.
     */
    protected static function renderDropdownActions(array $actions): string
    {
        $html = '<div class="dropdown">';
        $html .= '<button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">';
        $html .= '<i class="bi bi-three-dots"></i>';
        $html .= '</button>';
        $html .= '<ul class="dropdown-menu">';

        foreach ($actions as $action) {
            $html .= '<li><a class="dropdown-item" href="'.$action['url'].'">';
            $html .= '<i class="bi '.$action['icon'].' me-2"></i>'.$action['title'];
            $html .= '</a></li>';
        }

        $html .= '</ul></div>';

        return $html;
    }

    /**
     * Apply common DataTable styling scripts.
     */
    public static function stylingScript(?string $tableId = null): string
    {
        $id = $tableId ?: '$("table").first()';

        return <<<JS
            $(function() {
                var table = $('#{$tableId}').DataTable();

                $('#{$tableId}_filter input').addClass('form-control form-control-sm')
                    .attr('style', 'width: 200px; display: inline-block; margin-left: 0.5rem;');
                $('#{$tableId}_filter label').addClass('mb-0');
                $('#{$tableId}_length select').addClass('form-select form-select-sm d-inline-block w-auto');

                table.on('draw.dt', function() {
                    $('.pagination').addClass('pagination-sm mb-0');
                });
            });
JS;
    }
}
