@extends('layouts.form.app')

@section('header')
<h1 class="m-0">Product Details</h1>
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
@endsection

@section('content')
<div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-box-seam me-2"></i>
                        Product Information
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="130">Name</th>
                                    <td>{{ $product->name }}</td>
                                </tr>
                                <tr>
                                    <th>SKU</th>
                                    <td><code>{{ $product->sku }}</code></td>
                                </tr>
                                <tr>
                                    <th>Category</th>
                                    <td>{{ $product->category }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        @if($product->status === 'active')
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-sm-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="130">Price</th>
                                    <td>${{ number_format((float) $product->price, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Stock</th>
                                    <td>
                                        {{ $product->stock }}
                                        @if($product->stock === 0)
                                            <span class="badge bg-danger ms-1">Out of Stock</span>
                                        @elseif($product->stock < 10)
                                            <span class="badge bg-warning ms-1">Low Stock</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($product->description)
                        <div class="mt-3">
                            <h6 class="text-muted">Description</h6>
                            <p class="mb-0">{{ $product->description }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-clock-history me-2"></i>
                        Record Info
                    </h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">ID:</td>
                            <td>{{ $product->id }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Created:</td>
                            <td>{{ $product->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Updated:</td>
                            <td>{{ $product->updated_at->format('M d, Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('products.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Products
        </a>
        @can('products.update')
            <a href="{{ route('products.edit', $product) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i> Edit Product
            </a>
        @endcan
    </div>
@endsection
