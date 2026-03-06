@extends('layouts.form.app')

@section('header')
    <h1 class="m-0">{{ $editing ? 'Edit Employee Category' : 'Add Employee Category' }}</h1>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('employee-categories.index') }}">Employee Categories</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $editing ? 'Edit' : 'Create' }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-{{ $editing ? 'pencil' : 'grid' }} me-2"></i>
                        {{ $editing ? 'Edit Employee Category: ' . $record->name : 'New Employee Category' }}
                    </h3>
                </div>
                <form action="{{ $editing ? route('employee-categories.update', $record) : route('employee-categories.store') }}"
                      method="POST">
                    @csrf
                    @if($editing) @method('PUT') @endif

                    <div class="card-body">
                        <div class="row">
                            @include('layouts.form.inputs.text', ['var' => [
                                'name'        => 'name',
                                'label'       => 'Name',
                                'value'       => $record?->name,
                                'placeholder' => 'Enter name',
                                'div'         => 'col-md-12',
                                'required'    => true,
                                'autofocus'   => true,
                            ]])
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>
                            {{ $editing ? 'Update' : 'Create' }} Employee Category
                        </button>
                        <a href="{{ route('employee-categories.index') }}" class="btn btn-secondary">
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

                @can('employee-categories.delete')
                    <div class="card card-danger card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Danger Zone
                            </h3>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small">Once deleted, this record cannot be recovered.</p>
                            <form action="{{ route('employee-categories.destroy', $record) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Are you sure you want to delete ' + '{{ $record->name }}' + '?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="bi bi-trash me-1"></i> Delete Employee Category
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
                            <i class="bi bi-dot"></i>
                            Fields marked with <span class="text-danger">*</span> are required.
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
