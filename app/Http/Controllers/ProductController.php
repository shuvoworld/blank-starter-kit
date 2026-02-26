<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        // Verify the current user can view the product list before loading any data.
        $this->authorize('viewAny', Product::class);

        if ($request->ajax()) {
            return DataTables::of(Product::query())
                ->addIndexColumn()
                ->addColumn('price_formatted', function (Product $product) {
                    return '$'.number_format((float) $product->price, 2);
                })
                ->addColumn('status_badge', function (Product $product) {
                    $class = $product->status === 'active' ? 'bg-success' : 'bg-secondary';

                    return '<span class="badge '.$class.'">'.ucfirst($product->status).'</span>';
                })
                ->addColumn('action', function (Product $product) {
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
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('products.index');
    }

    public function create(): View
    {
        // Verify the current user can create products before showing the form.
        $this->authorize('create', Product::class);

        return view('products.create');
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        // Re-check on form submit to prevent direct POST requests bypassing the form.
        $this->authorize('create', Product::class);

        Product::create($request->validated());

        return to_route('products.index')->with('status', 'Product created successfully.');
    }

    public function show(Product $product): View
    {
        // Verify the current user can view this specific product record.
        $this->authorize('view', $product);

        return view('products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        // Verify the current user can edit this specific product record.
        $this->authorize('update', $product);

        return view('products.edit', compact('product'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        // Re-check on form submit to prevent direct PUT requests bypassing the form.
        $this->authorize('update', $product);

        $product->update($request->validated());

        return to_route('products.index')->with('status', 'Product updated successfully.');
    }

    public function destroy(Product $product): JsonResponse
    {
        // Verify the current user can delete this specific product record.
        $this->authorize('delete', $product);

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully.']);
    }
}
