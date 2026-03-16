<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use Illuminate\View\View;

class ProductController extends BaseController
{
    public function __construct(ProductDataTableController $dataTableController)
    {
        $this->model = Product::class;
        $this->routePrefix = 'products';
        $this->viewPrefix = 'products';
        $this->resourceName = 'Product';
        $this->dataTableController = $dataTableController;
    }

    protected function requestClass(): ?string
    {
        return ProductRequest::class;
    }

    public function show(int|string $record): View
    {
        $product = $this->findRecord($record);
        $this->authorizeAction('view', $product);

        return view('products.show', compact('product'));
    }
}
