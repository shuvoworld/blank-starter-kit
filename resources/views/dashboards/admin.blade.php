@extends('layouts.form.app')

@section('header')
<h1 class="m-0">Admin Dashboard</h1>
@endsection

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Dashboard</li>
@endsection

@section('content')
{{-- Small Boxes Row --}}
<div class="row">
    @if(isset($stats['employees']))
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3 class="text-white">{{ $stats['employees'] }}</h3>
                <p class="text-white">Employees</p>
            </div>
            <a href="{{ route('employees.index') }}" class="small-box-footer text-white">
                More info <i class="bi bi-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    @endif

    @if(isset($stats['activeEmployees']))
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3 class="text-white">{{ $stats['activeEmployees'] }}</h3>
                <p class="text-white">Active Employees</p>
            </div>
            <a href="{{ route('employees.index') }}" class="small-box-footer text-white">
                More info <i class="bi bi-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    @endif

    @if(isset($stats['pendingRequests']))
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3 class="text-white">{{ $stats['pendingRequests'] }}</h3>
                <p class="text-white">Pending Requests</p>
            </div>
            <a href="{{ route('leave-requests.index') }}" class="small-box-footer text-white">
                Review <i class="bi bi-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    @endif

    @if(isset($stats['approvedToday']))
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3 class="text-white">{{ $stats['approvedToday'] }}</h3>
                <p class="text-white">Approved Today</p>
            </div>
            <a href="{{ route('leave-requests.index') }}" class="small-box-footer text-white">
                View <i class="bi bi-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    @endif
</div>

{{-- Leave Management Overview Row --}}
@if(isset($leaveRequestsStats))
<div class="row">
    <div class="col-lg-6">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-calendar-check me-2"></i>Leave Requests Overview ({{ $currentYear }})
                </h3>
                <a href="{{ route('leave-requests.index') }}" class="btn btn-sm btn-outline-light float-right">View All</a>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4 mb-3">
                        <div class="description-block border-right">
                            <h5 class="description-header text-warning">{{ $leaveRequestsStats['pending'] }}</h5>
                            <span class="description-text">PENDING</span>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="description-block border-right">
                            <h5 class="description-header text-success">{{ $leaveRequestsStats['approved'] }}</h5>
                            <span class="description-text">APPROVED</span>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="description-block">
                            <h5 class="description-header text-danger">{{ $leaveRequestsStats['rejected'] }}</h5>
                            <span class="description-text">REJECTED</span>
                        </div>
                    </div>
                </div>
                <div class="progress mb-2">
                    <div class="progress-bar bg-success" style="width: {{ $leaveRequestsStats['total'] > 0 ? ($leaveRequestsStats['approved'] / $leaveRequestsStats['total'] * 100) : 0 }}%"></div>
                    <div class="progress-bar bg-warning" style="width: {{ $leaveRequestsStats['total'] > 0 ? ($leaveRequestsStats['pending'] / $leaveRequestsStats['total'] * 100) : 0 }}%"></div>
                    <div class="progress-bar bg-danger" style="width: {{ $leaveRequestsStats['total'] > 0 ? ($leaveRequestsStats['rejected'] / $leaveRequestsStats['total'] * 100) : 0 }}%"></div>
                </div>
                <small class="text-muted">{{ $leaveRequestsStats['total'] }} total requests</small>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-grid me-2"></i>Quick Stats
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Leave Types</span>
                            <strong class="text-primary">{{ $leaveTypesCount ?? 0 }}</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Holidays ({{ $currentYear }})</span>
                            <strong class="text-info">{{ $holidaysCount ?? 0 }}</strong>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Users w/ Balances</span>
                            <strong class="text-secondary">{{ $stats['usersWithBalances'] ?? 0 }}</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Departments</span>
                            <strong class="text-success">{{ \App\Models\Department::count() }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Main Content Row --}}
<div class="row">
    {{-- Left Column --}}
    <div class="col-md-8">
        {{-- Pending Leave Requests --}}
        @if(isset($pendingRequests) && $pendingRequests->isNotEmpty())
        <div class="card card-warning card-outline mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-clock-history me-1"></i> Pending Leave Requests
                </h3>
                <div class="card-tools float-right">
                    <a href="{{ route('leave-requests.index') }}" class="btn btn-tool btn-sm">View All</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Leave Type</th>
                                <th>Dates</th>
                                <th>Days</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingRequests as $request)
                            <tr>
                                <td>{{ $request->user->name }}</td>
                                <td><span class="badge bg-info">{{ $request->leaveType->code }}</span> {{ $request->leaveType->name }}</td>
                                <td>{{ $request->start_date->format('M d') }} - {{ $request->end_date->format('M d') }}</td>
                                <td>{{ $request->total_days }}</td>
                                <td>
                                    <a href="{{ route('leave-requests.show', $request) }}" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        {{-- Latest Employees Table --}}
        @if(isset($stats['recentEmployees']))
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
                            @forelse($stats['recentEmployees'] as $employee)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $employee->name }}</td>
                                    <td>{{ $employee->email }}</td>
                                    <td>{{ $employee->department ?? '—' }}</td>
                                    <td>{{ $employee->position ?? '—' }}</td>
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
        @endif

        {{-- Recent Activity Table --}}
        @if($recentActivities->isNotEmpty())
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
                            @foreach($recentActivities as $activity)
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
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
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
                    <a href="{{ route('leave-types.create') }}" class="btn btn-primary">
                        <i class="bi bi-calendar-check me-1"></i> Add Leave Type
                    </a>
                    <a href="{{ route('holidays.create') }}" class="btn btn-secondary">
                        <i class="bi bi-calendar-event me-1"></i> Add Holiday
                    </a>
                    <a href="{{ route('leave-requests.index', ['status' => 'pending']) }}" class="btn btn-warning">
                        <i class="bi bi-clock-history me-1"></i> Review Requests
                    </a>
                </div>
            </div>
        </div>

        {{-- Upcoming Holidays --}}
        @if(isset($upcomingHolidays) && $upcomingHolidays->isNotEmpty())
        <div class="card card-info card-outline mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-calendar-event me-1"></i> Upcoming Holidays
                </h3>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    @foreach($upcomingHolidays->take(5) as $holiday)
                    <div class="list-group-item px-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $holiday->name }}</strong>
                                <br>
                                <small class="text-muted">{{ $holiday->date->format('D, M d') }}</small>
                            </div>
                            @if($holiday->is_recurring)
                                <span class="badge bg-info">Recurring</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                <a href="{{ route('holidays.index') }}" class="btn btn-sm btn-outline-info w-100 mt-2">View All Holidays</a>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
