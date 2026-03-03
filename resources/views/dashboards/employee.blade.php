@extends('layouts.form.app')

@section('header')
<h1 class="m-0">My Dashboard</h1>
@endsection

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Dashboard</li>
@endsection

@section('content')
<div class="row">
    {{-- Left Column --}}
    <div class="col-md-8">
        {{-- Leave Balance Summary Card --}}
        <div class="card card-primary card-outline mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">
                    <i class="bi bi-pie-chart me-1"></i> My Leave Balance
                </h3>
                <a href="{{ route('my-leave-summary') }}" class="btn btn-sm btn-outline-light">
                    <i class="bi bi-fullscreen me-1"></i> View Full Summary
                </a>
            </div>
            <div class="card-body">
                @if(isset($leaveBalances) && $leaveBalances->isNotEmpty())
                    <div class="row">
                        @foreach($leaveBalances as $balance)
                            <div class="col-md-6 mb-2">
                                <div class="card card-outline">
                                    <div class="card-header py-2">
                                        <h6 class="card-title mb-0">
                                            <span class="badge bg-info me-2">{{ $balance->leaveType->code }}</span>
                                            {{ $balance->leaveType->name }}
                                        </h6>
                                    </div>
                                    <div class="card-body py-2">
                                        <div class="d-flex justify-content-between mb-1">
                                            <small>Entitlement:</small>
                                            <strong>{{ $balance->total_entitlement }}d</strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <small>Taken:</small>
                                            <strong class="text-danger">{{ $balance->taken_days }}d</strong>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <small>Remaining:</small>
                                            <strong class="text-success">{{ $balance->remaining_days }}d</strong>
                                        </div>
                                        @if($balance->pending_days > 0)
                                            <div class="d-flex justify-content-between">
                                                <small>Pending:</small>
                                                <strong class="text-warning">{{ $balance->pending_days }}d</strong>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Your leave balances will be displayed here once you have leave types assigned or submit your first request.
                    </div>
                @endif
            </div>
        </div>

        {{-- My Leave Requests Card --}}
        <div class="card card-success card-outline mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">
                    <i class="bi bi-calendar2-check me-1"></i> My Leave Requests
                </h3>
                <a href="{{ route('my-leave-requests.create') }}" class="btn btn-sm btn-outline-light">
                    <i class="bi bi-plus-lg me-1"></i> New Request
                </a>
            </div>
            <div class="card-body">
                @if(isset($pendingRequests) && $pendingRequests->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Leave Type</th>
                                    <th>Dates</th>
                                    <th>Days</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingRequests as $request)
                                    <tr>
                                        <td>
                                            <span class="badge bg-info">{{ $request->leaveType->code }}</span>
                                        </td>
                                        <td>{{ $request->start_date->format('M d') }} - {{ $request->end_date->format('M d, Y') }}</td>
                                        <td>{{ $request->total_days }}</td>
                                        <td>{!! $request->status_badge !!}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <a href="{{ route('my-leave-requests.index') }}" class="btn btn-sm btn-outline-secondary float-end mt-2">
                        View All Requests <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-calendar2-check fs-1 text-muted d-block mb-3"></i>
                        <h6 class="text-muted">No Leave Requests</h6>
                        <p class="text-muted">Submit your first leave request to get started.</p>
                        <a href="{{ route('my-leave-requests.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i> Create Request
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Upcoming Holidays Card --}}
        <div class="card card-warning card-outline mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-calendar-event me-1"></i> Upcoming Holidays
                </h3>
            </div>
            <div class="card-body">
                @if(isset($upcomingHolidays) && $upcomingHolidays->isNotEmpty())
                    <div class="list-group">
                        @foreach($upcomingHolidays as $holiday)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $holiday->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $holiday->date->format('l, F j, Y') }}</small>
                                </div>
                                @if($holiday->is_recurring)
                                    <span class="badge bg-info">Recurring</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted small mb-0">No upcoming holidays in the next 30 days.</p>
                @endif
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
                        <i class="bi bi-person-circle fs-1 text-primary"></i>
                    </div>
                </div>
                <h3 class="profile-username text-center">{{ Auth::user()->name }}</h3>
                <p class="text-muted text-center">Employee</p>
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
                <h3 class="card-title">Quick Access</h3>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('my-leave-requests.create') }}" class="btn btn-outline-primary">
                        <i class="bi bi-calendar-plus me-1"></i> Apply for Leave
                    </a>
                    <a href="{{ route('my-leave-requests.index') }}" class="btn btn-outline-info">
                        <i class="bi bi-calendar2-check me-1"></i> My Requests
                    </a>
                    <a href="{{ route('my-leave-summary') }}" class="btn btn-outline-success">
                        <i class="bi bi-pie-chart me-1"></i> Leave Summary
                    </a>
                    <a href="{{ route('holidays.index') }}" class="btn btn-outline-warning">
                        <i class="bi bi-calendar-event me-1"></i> Holidays
                    </a>
                </div>
            </div>
        </div>

        {{-- Leave Stats Widget --}}
        <div class="card card-info card-outline mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-bar-chart me-1"></i> Leave Stats ({{ $currentYear }})
                </h3>
            </div>
            <div class="card-body">
                @if(isset($leaveStats))
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Entitlement:</span>
                        <strong class="text-primary">{{ $leaveStats['total_entitlement'] ?? 0 }} days</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Taken:</span>
                        <strong class="text-danger">{{ $leaveStats['total_taken'] ?? 0 }} days</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Remaining:</span>
                        <strong class="text-success">{{ $leaveStats['remaining'] ?? 0 }} days</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Pending:</span>
                        <strong class="text-warning">{{ $leaveStats['pending'] ?? 0 }} days</strong>
                    </div>
                @else
                    <p class="text-muted small mb-0">No leave statistics available.</p>
                @endif
            </div>
        </div>

        {{-- Team Directory Widget --}}
        <div class="card card-secondary card-outline mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-people me-1"></i> Team Directory
                </h3>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-2">View your team members and their contact information.</p>
                <a href="{{ route('employees.index') }}" class="btn btn-info btn-block">
                    <i class="bi bi-list-ul me-1"></i> View Directory
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
