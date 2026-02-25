@extends('layouts.app')

@section('header')
<h1 class="m-0">Admin Dashboard</h1>
@endsection

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Dashboard</li>
@endsection

@section('content')
{{-- Small Boxes Row --}}
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3 class="text-white">{{ $stats['employees'] ?? 0 }}</h3>
                <p class="text-white">Employees</p>
            </div>
            <a href="{{ route('employees.index') }}" class="small-box-footer text-white">
                More info <i class="bi bi-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3 class="text-white">{{ $stats['activeEmployees'] ?? 0 }}</h3>
                <p class="text-white">Active Employees</p>
            </div>
            <a href="{{ route('employees.index') }}" class="small-box-footer text-white">
                More info <i class="bi bi-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3 class="text-white">{{ $stats['schedules'] ?? 0 }}</h3>
                <p class="text-white">Schedules</p>
            </div>
            <a href="#" class="small-box-footer text-white">
                More info <i class="bi bi-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3 class="text-white">{{ $stats['shifts'] ?? 0 }}</h3>
                <p class="text-white">Shifts</p>
            </div>
            <a href="#" class="small-box-footer text-white">
                More info <i class="bi bi-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

{{-- Main Content Row --}}
<div class="row">
    {{-- Left Column --}}
    <div class="col-md-8">
        {{-- Latest Employees Table --}}
        <div class="card card-success card-outline mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-person-badge me-1"></i> Latest Employees
                </h3>
                <div class="card-tools float-right">
                    <a href="{{ route('employees.index') }}" class="btn btn-tool"><i class="bi bi-list-ul"></i></a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th style="width: 10px">#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Department</th>
                                <th>Position</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($stats['recentEmployees'] ?? collect()) as $employee)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $employee->name }}</td>
                                    <td>{{ $employee->email }}</td>
                                    <td>{{ $employee->department }}</td>
                                    <td>{{ $employee->position }}</td>
                                    <td>
                                        @if($employee->status === 'active')
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">{{ ucfirst($employee->status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="bi bi-person-x fs-2 d-block mb-2 opacity-25"></i>
                                        <p class="mb-0">No employees found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Recent Activity Table --}}
        <div class="card card-primary card-outline mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-list me-1"></i> Recent Activity Log
                </h3>
                <div class="card-tools float-right">
                    <a href="{{ route('activity-log.index') }}" class="btn btn-tool"><i class="bi bi-list-ul"></i></a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th style="width: 10px">#</th>
                                <th>Subject</th>
                                <th>Action</th>
                                <th>Causer</th>
                                <th style="width: 150px">Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentActivities as $activity)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <span class="fw-semibold">{{ $activity->subject_type ? class_basename($activity->subject_type) : 'N/A' }}</span>
                                        @if($activity->subject)
                                            <small class="text-muted d-block">{{ optional($activity->subject)->name ?? optional($activity->subject)->title ?? $activity->subject_id }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($activity->description === 'created')
                                            <span class="badge bg-success">Created</span>
                                        @elseif($activity->description === 'updated')
                                            <span class="badge bg-warning">Updated</span>
                                        @elseif($activity->description === 'deleted')
                                            <span class="badge bg-danger">Deleted</span>
                                        @else
                                            <span class="badge bg-info">{{ $activity->description }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($activity->causer)
                                            <span class="text-sm">{{ $activity->causer->name }}</span>
                                        @else
                                            <span class="text-muted text-sm">System</span>
                                        @endif
                                    </td>
                                    <td class="text-sm text-muted">{{ $activity->created_at->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="bi bi-clock-history fs-2 d-block mb-2 opacity-25"></i>
                                        <p class="mb-0">No recent activity found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Right Column --}}
    <div class="col-md-4">
        {{-- Profile Widget --}}
        <div class="card card-primary card-outline mb-3">
            <div class="card-body box-profile">
                <div class="text-center">
                    <div class="bg-primary bg-opacity-10 rounded-circle p-3 d-inline-block">
                        <i class="bi bi-person-fill-gear fs-1 text-primary"></i>
                    </div>
                </div>
                <h3 class="profile-username text-center">{{ Auth::user()->name }}</h3>
                <p class="text-muted text-center">Administrator</p>
                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Roles</b> <a class="float-right">
                            @foreach(Auth::user()->roles as $role)
                                <span class="badge bg-primary">{{ $role->name }}</span>
                            @endforeach
                        </a>
                    </li>
                    <li class="list-group-item">
                        <b>Member Since</b> <a class="float-right">{{ Auth::user()->created_at->format('M Y') }}</a>
                    </li>
                </ul>
                <a href="{{ route('profile.edit') }}" class="btn btn-primary btn-block"><b>Edit Profile</b></a>
            </div>
        </div>

        {{-- Quick Actions Widget --}}
        <div class="card card-warning card-outline mb-3">
            <div class="card-header">
                <h3 class="card-title">Quick Actions</h3>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('employees.create') }}" class="btn btn-success">
                        <i class="bi bi-person-badge me-1"></i> Add Employee
                    </a>
                    <a href="#" class="btn btn-info">
                        <i class="bi bi-calendar3 me-1"></i> Create Schedule
                    </a>
                    <a href="#" class="btn btn-warning">
                        <i class="bi bi-clock me-1"></i> Manage Shifts
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
