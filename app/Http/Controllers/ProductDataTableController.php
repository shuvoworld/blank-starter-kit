<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController\BaseDataTableController;
use App\Models\Product;

class ProductDataTableController extends BaseDataTableController
{
    public function __construct()
    {
        $this->model = Product::class;
        $this->routePrefix = 'products';
        $this->rawColumns = ['status_badge', 'price_formatted', 'updated_at', 'updated_by'];
    }

    protected function indexQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return Product::query()->with('updatedBy');
    }

    protected function dataTableColumns(): array
    {
        return [
            'price_formatted' => fn (Product $product) => '$'.number_format((float) $product->price, 2),

            'status_badge' => fn (Product $product) => '<span class="badge '.($product->status === 'active' ? 'bg-success' : 'bg-secondary').'">'.ucfirst($product->status).'</span>',

            'updated_at' => fn (Product $product) => $product->updated_at->format('M d, Y H:i'),

            'updated_by' => fn (Product $product) => $product->updatedBy?->name ?? '—',
        ];
    }

    protected function actionColumn($product): string
    {
        $showUrl = route('products.show', $product);
        $editUrl = route('products.edit', $product);
        $deleteUrl = route('products.destroy', $product);

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
                    data-name="'.e($product->name).'" title="Delete">
                    <i class="bi bi-trash"></i>
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
                'data' => 'name',
                'name' => 'name',
                'label' => 'Name',
            ],
            [
                'data' => 'status_badge',
                'name' => 'status',
                'label' => 'Status',
                'orderable' => false,
                'searchable' => false,
                'className' => 'text-center',
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
                'width' => '150',
                'orderable' => false,
                'searchable' => false,
                'className' => 'text-center',
            ],
        ];
    }
}
