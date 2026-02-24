@extends('layouts.app')

@section('header')
<h1 class="m-0">Admin Dashboard</h1>
@endsection

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Dashboard</li>
@endsection

@section('content')
{{-- Info Boxes Row --}}
<div class="row">
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-info elevation-1"><i class="bi bi-people"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Users</span>
                <span class="info-box-number">{{ $stats['users'] ?? 0 }}</span>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-success elevation-1"><i class="bi bi-person-badge"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Employees</span>
                <span class="info-box-number">{{ $stats['employees'] ?? 0 }}</span>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-warning elevation-1"><i class="bi bi-box-seam"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Products</span>
                <span class="info-box-number">{{ $stats['products'] ?? 0 }}</span>
            </div>
        </div>
    </div>
    @can('manage landing page')
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-danger elevation-1"><i class="bi bi-layout-sidebar"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Site Sections</span>
                    <span class="info-box-number">{{ $stats['landingSections'] ?? 0 }}</span>
                </div>
            </div>
        </div>
    @endcan
</div>

{{-- Small Boxes Row --}}
<div class="row">
    @can('view any users')
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['users'] ?? 0 }}</h3>
                    <p>Total Users</p>
                </div>
                <div class="icon">
                    <i class="bi bi-people"></i>
                </div>
                <a href="{{ route('users.index') }}" class="small-box-footer">
                    More info <i class="bi bi-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    @endcan

    @can('view any employees')
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['activeEmployees'] ?? 0 }}<sup style="font-size: 20px">%</sup></h3>
                    <p>Active Rate</p>
                </div>
                <div class="icon">
                    <i class="bi bi-person-badge"></i>
                </div>
                <a href="{{ route('employees.index') }}" class="small-box-footer">
                    More info <i class="bi bi-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    @endcan

    @can('view any products')
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['inStockProducts'] ?? 0 }}</h3>
                    <p>In Stock</p>
                </div>
                <div class="icon">
                    <i class="bi bi-box-seam"></i>
                </div>
                <a href="{{ route('products.index') }}" class="small-box-footer">
                    More info <i class="bi bi-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    @endcan

    @can('manage landing page')
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['landingSections'] ?? 0 }}</h3>
                    <p>Site Sections</p>
                </div>
                <div class="icon">
                    <i class="bi bi-layout-sidebar"></i>
                </div>
                <a href="{{ route('landing-page-sections.index') }}" class="small-box-footer">
                    More info <i class="bi bi-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    @endcan
</div>

{{-- Product List Cards --}}
<div class="row">
    @can('view any users')
        <div class="col-md-4">
            <div class="card card-info card-outline mb-3">
                <div class="card-header">
                    <h5 class="card-title">Users</h5>
                    <div class="card-tools float-right">
                        <a href="{{ route('users.index') }}" class="btn btn-tool"><i class="bi bi-list-ul"></i></a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-sm-12">
                            <div class="progress mb-2">
                                <div class="progress-bar bg-info" role="progressbar" style="width: {{ min($stats['users'] ?? 0, 100) }}%" aria-valuenow="{{ $stats['users'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                    <h5 class="mb-0">{{ $stats['users'] ?? 0 }}</h5>
                    <small class="text-muted">Total Users</small>
                </div>
            </div>
        </div>
    @endcan

    @can('view any employees')
        <div class="col-md-4">
            <div class="card card-success card-outline mb-3">
                <div class="card-header">
                    <h5 class="card-title">Employees</h5>
                    <div class="card-tools float-right">
                        <span class="badge badge-success">{{ $stats['activeEmployees'] ?? 0 }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-sm-12">
                            <div class="progress mb-2">
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ min((($stats['activeEmployees'] ?? 0) / max($stats['employees'] ?? 1, 1)) * 100, 100) }}%" aria-valuenow="{{ $stats['activeEmployees'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                    <h5 class="mb-0">{{ $stats['activeEmployees'] ?? 0 }}</h5>
                    <small class="text-muted">Active Employees</small>
                </div>
            </div>
        </div>
    @endcan

    @can('manage landing page')
        <div class="col-md-4">
            <div class="card card-warning card-outline mb-3">
                <div class="card-header">
                    <h5 class="card-title">Landing Page</h5>
                    <div class="card-tools float-right">
                        <a href="{{ route('landing-page-sections.index') }}" class="btn btn-tool"><i class="bi bi-gear"></i></a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-sm-12">
                            <div class="progress mb-2">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                    <h5 class="mb-0">{{ $stats['landingSections'] ?? 0 }}</h5>
                    <small class="text-muted">Customizable Sections</small>
                </div>
            </div>
        </div>
    @endcan
</div>

{{-- Main Content Row --}}
<div class="row">
    {{-- Left Column --}}
    <div class="col-md-8">
        {{-- Chart Card --}}
        <div class="card card-success card-outline mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-bar-chart me-1"></i> Organization Overview
                </h3>
                <div class="card-tools float-right">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="bi bi-dash"></i>
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="chart">
                    <canvas id="orgChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>

        {{-- Recent Activity Table --}}
        @can('view any activity log')
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
        @endcan
    </div>

    {{-- Right Column --}}
    <div class="col-md-4">
        {{-- Profile Widget --}}
        <div class="card card-primary card-outline mb-3">
            <div class="card-body box-profile">
                <div class="text-center">
                    <div class="bg-primary bg-opacity-10 rounded-circle p-3 d-inline-block">
                        <i class="bi bi-shield-fill fs-1 text-primary"></i>
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
                    @can('create users')
                        <a href="{{ route('users.create') }}" class="btn btn-info">
                            <i class="bi bi-person-plus me-1"></i> Add User
                        </a>
                    @endcan
                    @can('manage landing page')
                        <a href="{{ route('landing-page-sections.index') }}" class="btn btn-warning">
                            <i class="bi bi-palette me-1"></i> Customize Site
                        </a>
                    @endcan
                    @can('view any roles')
                        <a href="{{ route('roles.index') }}" class="btn btn-success">
                            <i class="bi bi-shield me-1"></i> Manage Roles
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
  $(function () {
    'use strict'

    var orgChartCanvas = document.getElementById('orgChart').getContext('2d')

    var orgChartData = {
      labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
      datasets: [
        {
          label: 'Users',
          backgroundColor: 'rgba(60,141,188,0.1)',
          borderColor: 'rgba(60,141,188,0.8)',
          pointRadius: false,
          pointColor: '#3b8bba',
          pointStrokeColor: 'rgba(60,141,188,1)',
          pointHighlightFill: '#fff',
          pointHighlightStroke: 'rgba(60,141,188,1)',
          data: [28, 48, 40, 19, 86, 27, 90]
        },
        {
          label: 'Employees',
          backgroundColor: 'rgba(210, 214, 222, 0.1)',
          borderColor: 'rgba(210, 214, 222, 1)',
          pointRadius: false,
          pointColor: '#c1c7d1',
          pointStrokeColor: '#c1c7d1',
          pointHighlightFill: '#fff',
          pointHighlightStroke: 'rgba(220,220,220,1)',
          data: [65, 59, 80, 81, 56, 55, 40]
        }
      ]
    }

    var orgChartOptions = {
      maintainAspectRatio: false,
      responsive: true,
      plugins: {
        legend: {
          display: false
        }
      },
      scales: {
        xAxes: [{
          grid: {
            display: false
          }
        }],
        yAxes: [{
          grid: {
            display: false
          }
        }]
      }
    }

    new Chart(orgChartCanvas, {
      type: 'line',
      data: orgChartData,
      options: orgChartOptions
    })
  })
</script>
@endpush
