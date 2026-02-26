@extends('layouts.app')

@section('header')
<h1 class="m-0">Designation Details</h1>
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('designations.index') }}">Designations</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ $designation->name }}</li>
@endsection

@section('content')
<div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center text-white mb-3"
                         style="width: 120px; height: 120px; font-size: 3rem;">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <h3 class="card-title">{{ $designation->name }}</h3>
                    @if($designation->is_active)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-secondary">Inactive</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-person-badge me-2"></i>
                        Designation Information
                    </h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="180">Name</th>
                            <td>{{ $designation->name }}</td>
                        </tr>
                        <tr>
                            <th>Description</th>
                            <td>{{ $designation->description ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @if($designation->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Sort Order</th>
                            <td>{{ $designation->sort_order }}</td>
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
                            <td>{{ $designation->id }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Created:</td>
                            <td>{{ $designation->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Updated:</td>
                            <td>{{ $designation->updated_at->format('M d, Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('designations.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Designations
        </a>
        @can('update designations')
            <a href="{{ route('designations.edit', $designation) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i> Edit Designation
            </a>
        @endcan
    </div>
@endsection
