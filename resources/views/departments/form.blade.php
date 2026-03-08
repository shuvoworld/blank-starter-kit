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
                    @if($editing)
                        @method('PUT')
                    @endif

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
                @include('layouts.form.includes.audits', ['element' => $record])
                @include('layouts.form.includes.delete-warning', ['element' => $record,'module' => 'departments','moduleTitle'=>'Department'])
            @else
                @include('layouts.form.includes.information', ['element' => $record,'module' => 'departments','moduleTitle'=>'Department'])
            @endif
        </div>
    </div>
@endsection
