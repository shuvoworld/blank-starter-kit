@extends('layouts.form.app')

@section('header')
    <h1 class="m-0">{{ $editing ? 'Edit Department' : 'Add Department' }}</h1>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('departments.index') }}">Departments</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $editing ? 'Edit' : 'Create' }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-{{ $editing ? 'pencil' : 'building-plus' }} me-2"></i>
                        {{ $editing ? 'Edit Department: '.$record->name : 'New Department' }}
                    </h3>
                </div>
                <form action="{{ $editing ? route('departments.update', $record) : route('departments.store') }}"
                      method="POST">
                    @csrf
                    @if($editing) @method('PUT') @endif

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name', $record?->name) }}"
                                       placeholder="Department name" required autofocus>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description" name="description"
                                          rows="4" placeholder="Department description">{{ old('description', $record?->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="sort_order" class="form-label">Sort Order</label>
                                <input type="number" class="form-control @error('sort_order') is-invalid @enderror"
                                       id="sort_order" name="sort_order"
                                       value="{{ old('sort_order', $record?->sort_order ?? 0) }}"
                                       placeholder="0" min="0">
                                @error('sort_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Lower numbers appear first</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="is_active" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('is_active') is-invalid @enderror"
                                        id="is_active" name="is_active" required>
                                    <option value="1" {{ old('is_active', $record?->is_active ?? 1) == 1 ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('is_active', $record?->is_active ?? 1) == 0 ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('is_active')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>
                            {{ $editing ? 'Update' : 'Create' }} Department
                        </button>
                        <a href="{{ route('departments.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-lg me-1"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-4">
            @if($editing)
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="bi bi-info-circle me-2"></i>
                            Metadata
                        </h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="text-muted">ID:</td>
                                <td>{{ $record->id }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Created:</td>
                                <td>{{ $record->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Updated:</td>
                                <td>{{ $record->updated_at->format('M d, Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                @can('delete departments')
                    <div class="card card-danger card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Danger Zone
                            </h3>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small">Once deleted, this record cannot be recovered.</p>
                            <form action="{{ route('departments.destroy', $record) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Are you sure you want to delete {{ $record->name }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="bi bi-trash me-1"></i> Delete Department
                                </button>
                            </form>
                        </div>
                    </div>
                @endcan
            @else
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="bi bi-info-circle me-2"></i>
                            Information
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">
                            <i class="bi bi-dot"></i> Fields marked with <span class="text-danger">*</span> are required.
                        </p>
                        <p class="text-muted small">
                            <i class="bi bi-dot"></i> Department name must be unique.
                        </p>
                        <p class="text-muted small mb-0">
                            <i class="bi bi-dot"></i> Lower sort order numbers appear first.
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
