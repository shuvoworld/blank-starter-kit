@extends('layouts.app')

@section('header')
<h1 class="m-0">Employee Details</h1>
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('employees.index') }}">Employees</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ $employee->name }}</li>
@endsection

@section('content')
<div class="row">
        {{-- Profile Picture Card --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    @if($employee->getFirstMediaUrl('profile_picture'))
                        <img src="{{ $employee->getFirstMediaUrl('profile_picture') }}"
                             alt="{{ $employee->name }}"
                             class="rounded-circle img-fluid mb-3"
                             style="max-width: 200px; max-height: 200px; object-fit: cover;">
                    @else
                        <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center text-white mb-3"
                             style="width: 200px; height: 200px; font-size: 4rem;">
                            <i class="bi bi-person-fill"></i>
                        </div>
                    @endif
                    <h3 class="card-title">{{ $employee->name }}</h3>
                    <p class="text-muted">{{ $employee->position }}</p>
                    @if($employee->status === 'active')
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
                        Employee Information
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="140">Name</th>
                                    <td>{{ $employee->name }}</td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td><a href="mailto:{{ $employee->email }}">{{ $employee->email }}</a></td>
                                </tr>
                                <tr>
                                    <th>Phone</th>
                                    <td>{{ $employee->phone ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        @if($employee->status === 'active')
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
                                    <th width="140">Department</th>
                                    <td>{{ $employee->department }}</td>
                                </tr>
                                <tr>
                                    <th>Position</th>
                                    <td>{{ $employee->position }}</td>
                                </tr>
                                <tr>
                                    <th>Salary</th>
                                    <td>${{ number_format((float) $employee->salary, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Hire Date</th>
                                    <td>{{ $employee->hire_date->format('M d, Y') }}</td>
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
                            <td>{{ $employee->id }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Created:</td>
                            <td>{{ $employee->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Updated:</td>
                            <td>{{ $employee->updated_at->format('M d, Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Documents Section --}}
    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-file-earmark-pdf me-2"></i>
                        Documents
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        {{-- Resume --}}
                        <div class="col-md-4 mb-3">
                            <div class="card card-outline card-primary h-100">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-file-earmark-person me-2"></i>Resume/CV
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if($employee->getFirstMedia('resume'))
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-file-earmark-pdf-fill text-danger fs-1 me-3"></i>
                                            <div class="flex-grow-1">
                                                <p class="mb-1 fw-semibold">{{ $employee->getFirstMedia('resume')->file_name }}</p>
                                                <small class="text-muted">{{ $employee->getFirstMedia('resume')->human_readable_size }}</small>
                                            </div>
                                        </div>
                                        <a href="{{ $employee->getFirstMediaUrl('resume') }}"
                                           target="_blank"
                                           class="btn btn-sm btn-primary w-100 mt-2">
                                            <i class="bi bi-download me-1"></i> Download Resume
                                        </a>
                                    @else
                                        <p class="text-muted mb-0">
                                            <i class="bi bi-dash-circle me-2"></i>No resume uploaded
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Certificates --}}
                        <div class="col-md-4 mb-3">
                            <div class="card card-outline card-success h-100">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-award me-2"></i>Certificates
                                        @if($employee->getMedia('certificates')->count() > 0)
                                            <span class="badge bg-success float-right">{{ $employee->getMedia('certificates')->count() }}</span>
                                        @endif
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if($employee->getMedia('certificates')->count() > 0)
                                        <div class="list-group list-group-flush">
                                            @foreach($employee->getMedia('certificates') as $cert)
                                                <div class="list-group-item px-0 d-flex align-items-center">
                                                    <i class="bi bi-file-earmark-pdf-fill text-danger me-2"></i>
                                                    <div class="flex-grow-1 text-truncate">
                                                        <small class="text-truncate d-block" title="{{ $cert->file_name }}">
                                                            {{ \Illuminate\Support\Str::limit($cert->file_name, 30) }}
                                                        </small>
                                                        <small class="text-muted">{{ $cert->human_readable_size }}</small>
                                                    </div>
                                                    <a href="{{ $cert->getUrl() }}"
                                                       target="_blank"
                                                       class="btn btn-sm btn-outline-primary ms-2">
                                                        <i class="bi bi-download"></i>
                                                    </a>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted mb-0">
                                            <i class="bi bi-dash-circle me-2"></i>No certificates uploaded
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Other Documents --}}
                        <div class="col-md-4 mb-3">
                            <div class="card card-outline card-info h-100">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-files me-2"></i>Documents
                                        @if($employee->getMedia('documents')->count() > 0)
                                            <span class="badge bg-info float-right">{{ $employee->getMedia('documents')->count() }}</span>
                                        @endif
                                    </h5>
                                </div>
                                <div class="card-body">
                                    @if($employee->getMedia('documents')->count() > 0)
                                        <div class="list-group list-group-flush">
                                            @foreach($employee->getMedia('documents') as $doc)
                                                <div class="list-group-item px-0 d-flex align-items-center">
                                                    <i class="bi bi-file-earmark-pdf-fill text-danger me-2"></i>
                                                    <div class="flex-grow-1 text-truncate">
                                                        <small class="text-truncate d-block" title="{{ $doc->file_name }}">
                                                            {{ \Illuminate\Support\Str::limit($doc->file_name, 30) }}
                                                        </small>
                                                        <small class="text-muted">{{ $doc->human_readable_size }}</small>
                                                    </div>
                                                    <a href="{{ $doc->getUrl() }}"
                                                       target="_blank"
                                                       class="btn btn-sm btn-outline-primary ms-2">
                                                        <i class="bi bi-download"></i>
                                                    </a>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted mb-0">
                                            <i class="bi bi-dash-circle me-2"></i>No documents uploaded
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('employees.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Employees
        </a>
        @can('update employees')
            <a href="{{ route('employees.edit', $employee) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i> Edit Employee
            </a>
        @endcan
    </div>
@endsection
