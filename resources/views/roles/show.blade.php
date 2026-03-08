@extends('layouts.form.app')

@section('header')
<h1 class="m-0">Role Details</h1>
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Roles</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ $role->name }}</li>
@endsection

@section('content')
<div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-shield me-2"></i>
                        Role Information
                    </h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="150">Name</th>
                            <td><span class="badge bg-primary fs-6">{{ $role->name }}</span></td>
                        </tr>
                        <tr>
                            <th>Guard</th>
                            <td>{{ $role->guard_name }}</td>
                        </tr>
                        <tr>
                            <th>Description</th>
                            <td>{{ $role->description ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Users</th>
                            <td>{{ $role->users->count() }}</td>
                        </tr>
                        <tr>
                            <th>Permissions</th>
                            <td>{{ $role->permissions->count() }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-key me-2"></i>
                        Permissions ({{ $role->permissions->count() }})
                    </h3>
                </div>
                <div class="card-body">
                    @if($role->permissions->count() > 0)
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($role->permissions as $permission)
                                <span class="badge bg-info">{{ $permission->name }}</span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">No permissions assigned.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('roles.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Roles
        </a>
        @can('roles.update')
            <a href="{{ route('roles.edit', $role) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i> Edit Role
            </a>
        @endcan
    </div>
@endsection
