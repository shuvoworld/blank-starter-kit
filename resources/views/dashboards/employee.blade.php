@extends('layouts.app')

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
        {{-- My Schedule Card --}}
        <div class="card card-primary card-outline mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-calendar3 me-1"></i> My Schedule
                </h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    Welcome, {{ Auth::user()->name }}! This is your personal dashboard where you can view your assigned schedules.
                </div>

                {{-- Schedule placeholder - will be implemented when schedule module is created --}}
                <div class="text-center py-5">
                    <i class="bi bi-calendar3 fs-1 text-muted d-block mb-3"></i>
                    <h5 class="text-muted">No schedules assigned yet</h5>
                    <p class="text-muted">Your administrator will assign schedules to you.</p>
                </div>
            </div>
        </div>

        {{-- Upcoming Shifts Card --}}
        <div class="card card-success card-outline mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-clock-history me-1"></i> Upcoming Shifts
                </h3>
            </div>
            <div class="card-body">
                <div class="text-center py-5">
                    <i class="bi bi-clock fs-1 text-muted d-block mb-3"></i>
                    <h5 class="text-muted">No upcoming shifts</h5>
                    <p class="text-muted">Check back later for assigned shifts.</p>
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
                    <a href="#" class="btn btn-outline-primary">
                        <i class="bi bi-calendar-week me-1"></i> View My Schedule
                    </a>
                    <a href="#" class="btn btn-outline-info">
                        <i class="bi bi-clock-history me-1"></i> Shift History
                    </a>
                    <a href="{{ route('profile.edit') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-gear me-1"></i> Account Settings
                    </a>
                </div>
            </div>
        </div>

        {{-- Employee Directory Widget --}}
        <div class="card card-info card-outline mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-people me-1"></i> Team Directory
                </h3>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-2">View your team members and their contact information.</p>
                <a href="#" class="btn btn-info btn-block">
                    <i class="bi bi-list-ul me-1"></i> View Directory
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
