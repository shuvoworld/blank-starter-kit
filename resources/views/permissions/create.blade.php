@extends('layouts.app')

@section('header')
<h1 class="m-0">Create Permission</h1>
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('permissions.index') }}">Permissions</a></li>
        <li class="breadcrumb-item active" aria-current="page">Create</li>
@endsection

@section('content')
<div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="bi bi-key-plus me-2"></i>
                Create New Permission
            </h3>
        </div>
        <div class="card-body">
            <form action="{{ route('permissions.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Permission Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="e.g., create users" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Use format: "action module" (e.g., "create users", "edit posts")</div>
                        </div>

                        <div class="mb-3">
                            <label for="guard_name" class="form-label">Guard</label>
                            <select class="form-select @error('guard_name') is-invalid @enderror" id="guard_name" name="guard_name">
                                <option value="web" selected>Web</option>
                                <option value="api">API</option>
                            </select>
                            @error('guard_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="module" class="form-label">Module</label>
                            <input type="text" class="form-control @error('module') is-invalid @enderror" id="module" name="module" value="{{ old('module') }}" placeholder="e.g., users, posts, settings" list="modules-list">
                            <datalist id="modules-list">
                                @foreach($modules as $module)
                                    <option value="{{ $module }}">
                                @endforeach
                            </datalist>
                            @error('module')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('permissions.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Save Permission
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
