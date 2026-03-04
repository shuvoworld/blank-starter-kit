<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
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

    protected function authorizeAction(string $ability, ?Model $record = null): void
    {
        if ($record) {
            $this->authorize($ability, $record);
        } else {
            $this->authorize($ability, Product::class);
        }
    }

    public function show(int|string $record): View
    {
        $product = $this->findRecord($record);
        $this->authorize('view', $product);

        return view('products.show', compact('product'));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        Product::create($request->validated());

        return $this->successRedirect('created');
    }

    public function update(UpdateProductRequest $request, int|string $record): RedirectResponse
    {
        $product = $this->findRecord($record);
        $this->authorize('update', $product);

        $product->update($request->validated());

        return $this->successRedirect('updated');
    }
}
