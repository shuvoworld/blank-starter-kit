@extends('layouts.form.app')

@section('header')
<h1 class="m-0">Department Details</h1>
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('departments.index') }}">Departments</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ $department->name }}</li>
@endsection

@section('content')
<div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-building me-2"></i>
                        Department Information
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="140">Name</th>
                                    <td>{{ $department->name }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        @if($department->is_active)
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
                                    <th width="140">Description</th>
                                    <td>{{ $department->description ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th>Sort Order</th>
                                    <td>{{ $department->sort_order }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
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
                            <td>{{ $department->id }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Created:</td>
                            <td>{{ $department->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Updated:</td>
                            <td>{{ $department->updated_at->format('M d, Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('departments.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Departments
        </a>
        @can('update departments')
            <a href="{{ route('departments.edit', $department) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i> Edit Department
            </a>
        @endcan
    </div>
@endsection
