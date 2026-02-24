@extends('layouts.app')

@section('header')
<h1 class="m-0">Editor Dashboard</h1>
@endsection

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Dashboard</li>
@endsection

@section('content')
{{-- Info Boxes Row --}}
<div class="row">
    <div class="col-12 col-sm-6">
        <div class="info-box">
            <span class="info-box-icon bg-success elevation-1"><i class="bi bi-person-badge"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Employees</span>
                <span class="info-box-number">{{ $stats['employees'] ?? 0 }}</span>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6">
        <div class="info-box">
            <span class="info-box-icon bg-info elevation-1"><i class="bi bi-box-seam"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Products</span>
                <span class="info-box-number">{{ $stats['products'] ?? 0 }}</span>
            </div>
        </div>
    </div>
</div>

{{-- Small Boxes Row --}}
<div class="row">
    @can('view any employees')
        <div class="col-lg-6 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['employees'] ?? 0 }}</h3>
                    <p>Employees</p>
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
        <div class="col-lg-6 col-6">
            <div class="small-box bg-info">
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
</div>

 {{-- Product List Cards --}}
<div class="row">
    @can('view any employees')
        <div class="col-md-6">
            <div class="card card-success card-outline mb-3">
                <div class="card-header">
                    <h5 class="card-title">Employee Management</h5>
                    <div class="card-tools float-right">
                        <a href="{{ route('employees.index') }}" class="btn btn-tool"><i class="bi bi-list-ul"></i></a>
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

    @can('view any products')
        <div class="col-md-6">
            <div class="card card-info card-outline mb-3">
                <div class="card-header">
                    <h5 class="card-title">Product Management</h5>
                    <div class="card-tools float-right">
                        <a href="{{ route('products.index') }}" class="btn btn-tool"><i class="bi bi-list-ul"></i></a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-sm-12">
                            <div class="progress mb-2">
                                <div class="progress-bar bg-info" role="progressbar" style="width: {{ min((($stats['inStockProducts'] ?? 0) / max($stats['products'] ?? 1, 1)) * 100, 100) }}%" aria-valuenow="{{ $stats['inStockProducts'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                    <h5 class="mb-0">{{ $stats['inStockProducts'] ?? 0 }}</h5>
                    <small class="text-muted">Products In Stock</small>
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
                    <i class="bi bi-bar-chart me-1"></i> Content Overview
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
                    <canvas id="contentChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>

        {{-- Quick Access Cards --}}
        <div class="row">
            @can('view any employees')
                <div class="col-md-6">
                    <div class="card card-success card-outline mb-3">
                        <div class="card-header">
                            <h5 class="card-title">Employees</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3">Manage employee records and information.</p>
                            <div class="d-grid gap-2">
                                <a href="{{ route('employees.index') }}" class="btn btn-outline-success">
                                    <i class="bi bi-list-ul me-1"></i> View All Employees
                                </a>
                                @can('create employees')
                                    <a href="{{ route('employees.create') }}" class="btn btn-success">
                                        <i class="bi bi-person-plus me-1"></i> Add Employee
                                    </a>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            @endcan

            @can('view any products')
                <div class="col-md-6">
                    <div class="card card-info card-outline mb-3">
                        <div class="card-header">
                            <h5 class="card-title">Products</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3">Manage product catalog and inventory.</p>
                            <div class="d-grid gap-2">
                                <a href="{{ route('products.index') }}" class="btn btn-outline-info">
                                    <i class="bi bi-list-ul me-1"></i> View All Products
                                </a>
                                @can('create products')
                                    <a href="{{ route('products.create') }}" class="btn btn-info">
                                        <i class="bi bi-plus-circle me-1"></i> Add Product
                                    </a>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            @endcan
        </div>
    </div>

    {{-- Right Column --}}
    <div class="col-md-4">
        {{-- Profile Widget --}}
        <div class="card card-primary card-outline mb-3">
            <div class="card-body box-profile">
                <div class="text-center">
                    <div class="bg-primary bg-opacity-10 rounded-circle p-3 d-inline-block">
                        <i class="bi bi-pencil-square fs-1 text-primary"></i>
                    </div>
                </div>
                <h3 class="profile-username text-center">{{ Auth::user()->name }}</h3>
                <p class="text-muted text-center">Content Editor</p>
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
                    @can('create employees')
                        <a href="{{ route('employees.create') }}" class="btn btn-success">
                            <i class="bi bi-person-plus me-1"></i> Add Employee
                        </a>
                    @endcan
                    @can('create products')
                        <a href="{{ route('products.create') }}" class="btn btn-info">
                            <i class="bi bi-plus-circle me-1"></i> Add Product
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

    var contentChartCanvas = document.getElementById('contentChart').getContext('2d')

    var contentChartData = {
      labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
      datasets: [
        {
          label: 'Content Items',
          backgroundColor: 'rgba(40,167,69,0.1)',
          borderColor: 'rgba(40,167,69,0.8)',
          pointRadius: false,
          pointColor: '#28a745',
          pointStrokeColor: 'rgba(40,167,69,1)',
          pointHighlightFill: '#fff',
          pointHighlightStroke: 'rgba(40,167,69,1)',
          data: [28, 48, 40, 19, 86, 27, 90]
        }
      ]
    }

    var contentChartOptions = {
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

    new Chart(contentChartCanvas, {
      type: 'line',
      data: contentChartData,
      options: contentChartOptions
    })
  })
</script>
@endpush
