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
