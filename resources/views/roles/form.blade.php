@extends('layouts.form.app')

@section('header')
    <h1 class="m-0">{{ $editing ? 'Edit Role' : 'Create Role' }}</h1>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Roles</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $editing ? 'Edit' : 'Create' }}</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="bi bi-{{ $editing ? 'shield-gear' : 'shield-plus' }} me-2"></i>
                {{ $editing ? 'Edit Role: '.$record->name : 'Create New Role' }}
            </h3>
        </div>
        <div class="card-body">
            <form action="{{ $editing ? route('roles.update', $record) : route('roles.store') }}" method="POST">
                @csrf
                @if($editing) @method('PUT') @endif

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Role Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $record?->name) }}"
                                   required autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="guard_name" class="form-label">Guard</label>
                            <select class="form-select @error('guard_name') is-invalid @enderror"
                                    id="guard_name" name="guard_name">
                                <option value="web" {{ old('guard_name', $record?->guard_name ?? 'web') === 'web' ? 'selected' : '' }}>Web</option>
                                <option value="api" {{ old('guard_name', $record?->guard_name) === 'api' ? 'selected' : '' }}>API</option>
                            </select>
                            @error('guard_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="3">{{ old('description', $record?->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Permissions</label>
                    <div class="card">
                        <div class="card-body">
                            @foreach($permissions as $module => $modulePermissions)
                                <div class="mb-3">
                                    <h6 class="text-uppercase text-muted small">{{ $module ?: 'General' }}</h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($modulePermissions as $permission)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                       name="permissions[]" value="{{ $permission->id }}"
                                                       id="permission_{{ $permission->id }}"
                                                       {{ (old('permissions') ? in_array($permission->id, old('permissions')) : ($editing && in_array($permission->id, $rolePermissions ?? []))) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="permission_{{ $permission->id }}">
                                                    {{ $permission->name }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @error('permissions')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> {{ $editing ? 'Update' : 'Save' }} Role
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
