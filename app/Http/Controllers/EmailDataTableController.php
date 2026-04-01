<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController\BaseDataTableController;
use App\Models\Email;
use Illuminate\Database\Eloquent\Builder;

class EmailDataTableController extends BaseDataTableController
{
    public function __construct()
    {
        $this->model = Email::class;
        $this->routePrefix = 'emails';
    }

    protected function indexQuery(): Builder
    {
        return Email::query()
            ->with(['updatedBy'])
            ->orderBy('name');
    }

    protected function dataTableColumns(): array
    {
        return [
            'updated_by_name' => fn (Email $email) => $email->updatedBy?->name ?? '—',
        ];
    }

    protected function actionColumn($email): string
    {
        $showUrl   = route('emails.show', $email);
        $editUrl   = route('emails.edit', $email);
        $deleteUrl = route('emails.destroy', $email);

        return '
            <div class="btn-group btn-group-sm">
                <a href="' . $showUrl . '" class="btn btn-info" title="View">
                    <i class="bi bi-eye"></i>
                </a>
                <a href="' . $editUrl . '" class="btn btn-primary" title="Edit">
                    <i class="bi bi-pencil"></i>
                </a>
                <button type="button" class="btn btn-danger btn-delete"
                    data-url="' . $deleteUrl . '"
                    data-name="' . e($email->name) . '" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        ';
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
                'data'       => 'updated_at',
                'name'       => 'updated_at',
                'label'      => 'Last Updated',
                'orderable'  => true,
                'searchable' => false,
                'className'  => 'text-center',
            ],
            [
                'data'       => 'updated_by_name',
                'name'       => 'updated_by',
                'label'      => 'Updated By',
                'orderable'  => false,
                'searchable' => false,
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
}
