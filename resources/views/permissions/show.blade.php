@extends('layouts.form.app')

@section('header')
<h1 class="m-0">Permission Details</h1>
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('permissions.index') }}">Permissions</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ $permission->name }}</li>
@endsection

@section('content')
<div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-key me-2"></i>
                        Permission Information
                    </h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="150">Name</th>
                            <td><span class="badge bg-info fs-6">{{ $permission->name }}</span></td>
                        </tr>
                        <tr>
                            <th>Guard</th>
                            <td>{{ $permission->guard_name }}</td>
                        </tr>
                        <tr>
                            <th>Module</th>
                            <td>
                                @if($permission->module)
                                    <span class="badge bg-secondary">{{ $permission->module }}</span>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Description</th>
                            <td>{{ $permission->description ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Assigned Roles</th>
                            <td>{{ $permission->roles->count() }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-shield me-2"></i>
                        Roles ({{ $permission->roles->count() }})
                    </h3>
                </div>
                <div class="card-body">
                    @if($permission->roles->count() > 0)
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($permission->roles as $role)
                                <span class="badge bg-primary">{{ $role->name }}</span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">This permission is not assigned to any role.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('permissions.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Permissions
        </a>
        @can('permissions.update')
            <a href="{{ route('permissions.edit', $permission) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i> Edit Permission
            </a>
        @endcan
    </div>
@endsection
