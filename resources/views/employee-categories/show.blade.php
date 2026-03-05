@extends('layouts.form.app')

@section('header')
    <h1 class="m-0">Employee Category Details</h1>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('employee-categories.index') }}">Employee Categories</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $employeeCategory->name }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-grid me-2"></i>
                        Employee Category Information
                    </h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="160">Name</th>
                            <td>{{ $employeeCategory->name }}</td>
                        </tr>
                    </table>
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
                            <td>{{ $employeeCategory->id }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Created:</td>
                            <td>{{ $employeeCategory->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Updated:</td>
                            <td>{{ $employeeCategory->updated_at->format('M d, Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('employee-categories.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Employee Categories
        </a>
        @can('update employee categories')
            <a href="{{ route('employee-categories.edit', $employeeCategory) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i> Edit Employee Category
            </a>
        @endcan
    </div>
@endsection
