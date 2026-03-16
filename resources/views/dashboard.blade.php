@extends('layouts.app')

@section('header')
<h1 class="m-0">Developer Dashboard</h1>
@endsection

@section('breadcrumb')
<li class="breadcrumb-item active" aria-current="page">Dashboard</li>
@endsection

@section('content')
{{-- Welcome Banner --}}
    <div class="pk-welcome-banner mb-3">
        <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h5 class="fw-bold text-white mb-1">Welcome back, {{ Auth::user()->name }}!</h5>
                <p class="mb-0 text-white-50 text-sm">{{ now()->format('l, F j, Y') }} &mdash; Laravel 12 + RBAC + Activity Log Starter Kit</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                @can('employees.view')
                    <a href="{{ route('employees.create') }}" class="btn btn-sm btn-light fw-semibold">
                        <i class="bi bi-person-plus me-1"></i> Add Employee
                    </a>
                @endcan
                @can('products.view')
                    <a href="{{ route('products.create') }}" class="btn btn-sm btn-outline-light fw-semibold">
                        <i class="bi bi-plus-circle me-1"></i> Add Product
                    </a>
                @endcan
            </div>
        </div>
    </div>

    {{-- Stack Overview Cards --}}
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-start">
            <i class="bi bi-info-circle-fill fs-4 me-3 flex-shrink-0"></i>
            <div class="flex-grow-1">
                <h6 class="alert-heading fw-bold mb-1">Powered by Modern Laravel Stack</h6>
                <p class="mb-0 small">This starter kit demonstrates best practices using <strong>Laravel 12</strong>, <strong>Spatie Permission</strong>, <strong>Spatie Activity Log</strong>, and <strong>Laravel Boost (MCP)</strong> for rapid development.</p>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="row g-3 mb-3">
        {{-- Users --}}
        <div class="col-6 col-md-3">
            <div class="card pk-stat-card shadow-sm h-100">
                <div class="card-body d-flex align-items-start justify-content-between">
                    <div>
                        <div class="pk-stat-value">{{ number_format($stats['users']) }}</div>
                        <div class="pk-stat-label">Users</div>
                        <div class="pk-stat-sub">
                            <i class="bi bi-person-check me-1"></i>Registered accounts
                        </div>
                    </div>
                    <div class="pk-stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Employees --}}
        <div class="col-6 col-md-3">
            <div class="card pk-stat-card shadow-sm h-100">
                <div class="card-body d-flex align-items-start justify-content-between">
                    <div>
                        <div class="pk-stat-value">{{ number_format($stats['employees']) }}</div>
                        <div class="pk-stat-label">Employees</div>
                        <div class="pk-stat-sub">
                            <span class="pk-stat-pill bg-success bg-opacity-10 text-success">
                                <i class="bi bi-circle-fill" style="font-size:0.45rem"></i>
                                {{ $stats['activeEmployees'] }} active
                            </span>
                        </div>
                    </div>
                    <div class="pk-stat-icon bg-success bg-opacity-10 text-success">
                        <i class="bi bi-person-badge-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Products --}}
        <div class="col-6 col-md-3">
            <div class="card pk-stat-card shadow-sm h-100">
                <div class="card-body d-flex align-items-start justify-content-between">
                    <div>
                        <div class="pk-stat-value">{{ number_format($stats['products']) }}</div>
                        <div class="pk-stat-label">Products</div>
                        <div class="pk-stat-sub">
                            <span class="pk-stat-pill bg-info bg-opacity-10 text-info">
                                <i class="bi bi-circle-fill" style="font-size:0.45rem"></i>
                                {{ $stats['inStockProducts'] }} in stock
                            </span>
                        </div>
                    </div>
                    <div class="pk-stat-icon bg-info bg-opacity-10 text-info">
                        <i class="bi bi-box-seam-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Roles & Permissions --}}
        <div class="col-6 col-md-3">
            <div class="card pk-stat-card shadow-sm h-100">
                <div class="card-body d-flex align-items-start justify-content-between">
                    <div>
                        <div class="pk-stat-value">{{ $stats['roles'] }}</div>
                        <div class="pk-stat-label">Roles</div>
                        <div class="pk-stat-sub">
                            <i class="bi bi-key me-1"></i>{{ $stats['permissions'] }} permissions
                        </div>
                    </div>
                    <div class="pk-stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-shield-fill-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tech Stack Documentation --}}
    <div class="row g-3 mb-3">
        {{-- Spatie Permission --}}
        <div class="col-lg-6">
            <div class="card h-100 border-primary">
                <div class="card-header bg-primary bg-opacity-10">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-shield-lock-fill fs-4 text-primary me-2"></i>
                        <div>
                            <h6 class="mb-0 fw-bold">Spatie Permission</h6>
                            <small class="text-muted">Role & Permission Management</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Handles roles and permissions with database-backed storage, direct permission assignment, and hierarchical role-based access control.
                    </p>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="p-2 bg-light rounded text-center">
                                <i class="bi bi-gem fs-4 text-primary d-block mb-1"></i>
                                <small class="fw-semibold">{{ $stats['roles'] }} Roles</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 bg-light rounded text-center">
                                <i class="bi bi-key fs-4 text-warning d-block mb-1"></i>
                                <small class="fw-semibold">{{ $stats['permissions'] }} Permissions</small>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-light mb-0 small">
                        <strong>Usage:</strong>
                        <code class="ms-1">$user->hasPermissionTo('employees.create')</code>
                        <code class="ms-1 d-block mt-1">{{ '@can' }}('users.view')</code>
                    </div>
                    @can('roles.view')
                        <a href="{{ route('roles.index') }}" class="btn btn-sm btn-outline-primary w-100 mt-2">
                            <i class="bi bi-gear me-1"></i> Manage Roles
                        </a>
                    @endcan
                </div>
            </div>
        </div>

        {{-- Spatie Activity Log --}}
        <div class="col-lg-6">
            <div class="card h-100 border-success">
                <div class="card-header bg-success bg-opacity-10">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-clock-history fs-4 text-success me-2"></i>
                        <div>
                            <h6 class="mb-0 fw-bold">Spatie Activity Log</h6>
                            <small class="text-muted">Model Activity Tracking</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Automatically logs model changes (created/updated/deleted) with old/new values, causer info, and timestamps. Uses <code>HasActivityLog</code> trait.
                    </p>
                    <div class="bg-light rounded p-3 mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            <small class="fw-semibold">Models with Logging:</small>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-primary">Users</span>
                            <span class="badge bg-success">Employees</span>
                            <span class="badge bg-warning">Roles</span>
                            <span class="badge bg-info">Permissions</span>
                            <span class="badge bg-secondary">Products</span>
                        </div>
                    </div>
                    <div class="alert alert-light mb-0 small">
                        <strong>Trait:</strong>
                        <code class="ms-1">use App\Traits\HasActivityLog;</code>
                    </div>
                    @can('activity-log.view')
                        <a href="{{ route('activity-log.index') }}" class="btn btn-sm btn-outline-success w-100 mt-2">
                            <i class="bi bi-journal-text me-1"></i> View Activity Log
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    {{-- Laravel Boost & Module Pattern --}}
    <div class="row g-3 mb-3">
        {{-- Laravel Boost MCP --}}
        <div class="col-lg-4">
            <div class="card h-100 border-info">
                <div class="card-header bg-info bg-opacity-10">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-cpu-fill fs-4 text-info me-2"></i>
                        <div>
                            <h6 class="mb-0 fw-bold">Laravel Boost (MCP)</h6>
                            <small class="text-muted">Developer Tools Server</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        MCP server providing database queries, Artisan commands, error logs, Tinker execution, and version-specific documentation search.
                    </p>
                    <div class="mb-3">
                        <small class="fw-semibold d-block mb-2">Available Tools:</small>
                        <div class="d-flex flex-wrap gap-1">
                            <span class="badge bg-light text-dark border">database-query</span>
                            <span class="badge bg-light text-dark border">database-schema</span>
                            <span class="badge bg-light text-dark border">tinker</span>
                            <span class="badge bg-light text-dark border">list-routes</span>
                            <span class="badge bg-light text-dark border">search-docs</span>
                            <span class="badge bg-light text-dark border">read-log-entries</span>
                            <span class="badge bg-light text-dark border">last-error</span>
                        </div>
                    </div>
                    <div class="alert alert-light mb-0 small">
                        <strong>Version:</strong> PHP 8.4.16 + Laravel 12.52.0
                    </div>
                </div>
            </div>
        </div>

        {{-- Module Creation Pattern --}}
        <div class="col-lg-4">
            <div class="card h-100 border-warning">
                <div class="card-header bg-warning bg-opacity-10">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-diagram-3-fill fs-4 text-warning me-2"></i>
                        <div>
                            <h6 class="mb-0 fw-bold">Module Pattern</h6>
                            <small class="text-muted">CRUD Generator</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Consistent module structure with Model, Factory, Seeder, Controller, Form Requests, Views, DataTables integration, and permissions.
                    </p>
                    <div class="bg-light rounded p-2 mb-3">
                        <small class="fw-semibold d-block mb-2">Module Components:</small>
                        <ul class="small mb-0 ps-3">
                            <li>Eloquent Model + Factory</li>
                            <li>Form Request Validation</li>
                            <li>Resource Controller</li>
                            <li>DataTable + Filters</li>
                            <li>CRUD Views (index/create/edit/show)</li>
                            <li>Route Permissions</li>
                        </ul>
                    </div>
                    <div class="alert alert-light mb-0 small">
                        <strong>Modules:</strong> Employees, Products, Users
                    </div>
                </div>
            </div>
        </div>

        {{-- Testing & Quality --}}
        <div class="col-lg-4">
            <div class="card h-100 border-danger">
                <div class="card-header bg-danger bg-opacity-10">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-check2-square fs-4 text-danger me-2"></i>
                        <div>
                            <h6 class="mb-0 fw-bold">Testing & Quality</h6>
                            <small class="text-muted">Pest 4 + Pint</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Pest 4 for testing with factories. Laravel Pint for code formatting. Follows Laravel 12 conventions and strict typing.
                    </p>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="p-2 bg-light rounded text-center">
                                <i class="bi bi-bug fs-4 text-danger d-block mb-1"></i>
                                <small class="fw-semibold">Pest 4.4</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 bg-light rounded text-center">
                                <i class="bi bi-brush fs-4 text-primary d-block mb-1"></i>
                                <small class="fw-semibold">Pint 1.27</small>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-light mb-0 small">
                        <strong>Commands:</strong>
                        <code class="ms-1 d-block mt-1">php artisan test --compact</code>
                        <code class="d-block mt-1">vendor/bin/pint --dirty</code>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Activity Log --}}
    @can('activity-log.view')
        <div class="row g-3 mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="bi bi-clock-history me-2 text-success"></i> Recent Activity
                        </h3>
                        <a href="{{ route('activity-log.index') }}" class="btn btn-sm btn-outline-secondary">
                            View All
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Subject</th>
                                        <th>Action</th>
                                        <th>Causer</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $recentActivities = \Spatie\Activitylog\Models\Activity::with('causer')
                                            ->latest()
                                            ->limit(8)
                                            ->get();
                                    @endphp
                                    @forelse($recentActivities as $activity)
                                        <tr>
                                            <td>
                                                <span class="fw-semibold">{{ $activity->subject_type ? class_basename($activity->subject_type) : 'N/A' }}</span>
                                                @if($activity->subject_type && class_exists($activity->subject_type) && $activity->subject)
                                                    <small class="text-muted d-block">{{ optional($activity->subject)->name ?? optional($activity->subject)->title ?? $activity->subject_id }}</small>
                                                @elseif($activity->subject_id)
                                                    <small class="text-muted d-block text-secondary">#{{ $activity->subject_id }}</small>
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
                                            <td colspan="4" class="text-center text-muted py-4">
                                                <i class="bi bi-clock-history fs-2 d-block mb-2 opacity-25"></i>
                                                No recent activity
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endcan

    {{-- Code Examples Section --}}
    <div class="row g-3 mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-code-slash me-2 text-primary"></i> Code Examples & Patterns
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        {{-- Permission Check --}}
                        <div class="col-md-6">
                            <h6 class="fw-semibold mb-2">
                                <i class="bi bi-shield-check text-success me-1"></i> Permission Check (Blade)
                            </h6>
                            <pre class="bg-dark text-light rounded p-3 mb-0"><code class="small">{{ '@can' }}('employees.view')
    // User can view employees
{{ '@endcan' }}

{{ '@can' }}('employees.create')
    <a href="{{ route('employees.create') }}">Add Employee</a>
{{ '@endcan' }}</code></pre>
                        </div>

                        {{-- Middleware --}}
                        <div class="col-md-6">
                            <h6 class="fw-semibold mb-2">
                                <i class="bi bi-lock text-warning me-1"></i> Route Middleware
                            </h6>
                            <pre class="bg-dark text-light rounded p-3 mb-0"><code class="small">Route::middleware('permission:view any employees')
    ->group(function () {
        Route::get('/', [EmployeeController::class, 'index']);
        Route::post('/', [EmployeeController::class, 'store'])
            ->middleware('permission:create employees');
    });</code></pre>
                        </div>

                        {{-- Activity Log Trait --}}
                        <div class="col-md-6">
                            <h6 class="fw-semibold mb-2">
                                <i class="bi bi-journal text-info me-1"></i> Activity Log Trait
                            </h6>
                            <pre class="bg-dark text-light rounded p-3 mb-0"><code class="small">// In Model
use App\Traits\HasActivityLog;

class Employee extends Model
{
    use HasActivityLog;

    protected $fillable = ['name', 'email', ...];
    // Logs: created, updated, deleted automatically
}</code></pre>
                        </div>

                        {{-- DataTable Filter Scopes --}}
                        <div class="col-md-6">
                            <h6 class="fw-semibold mb-2">
                                <i class="bi bi-funnel text-primary me-1"></i> Filter Scopes Pattern
                            </h6>
                            <pre class="bg-dark text-light rounded p-3 mb-0"><code class="small">// In Model
public function scopeByDepartment(
    Builder $query,
    ?string $department
): Builder {
    if (empty($department)) {
        return $query;
    }
    return $query->where('department', $department);
}</code></pre>
                        </div>

                        {{-- Laravel Boost Tinker --}}
                        <div class="col-md-6">
                            <h6 class="fw-semibold mb-2">
                                <i class="bi bi-terminal text-danger me-1"></i> Laravel Boost Tinker
                            </h6>
                            <pre class="bg-dark text-light rounded p-3 mb-0"><code class="small">// Quick debugging via Boost
mcp-laravel-boost_tinker:
    Employee::byDepartment('IT')->count();
    // Result: 6

    User::hasRole('Admin')->get();
    // Returns all admin users</code></pre>
                        </div>

                        {{-- Form Request Validation --}}
                        <div class="col-md-6">
                            <h6 class="fw-semibold mb-2">
                                <i class="bi bi-check-circle text-success me-1"></i> Form Request Pattern
                            </h6>
                            <pre class="bg-dark text-light rounded p-3 mb-0"><code class="small">class StoreEmployeeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees',
            'department' => 'required|string',
            'status' => 'in:active,inactive',
        ];
    }
}</code></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- User Permissions & Roles --}}
    <div class="row g-3 mb-3">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-person-badge me-2 text-primary"></i> Your Roles
                    </h3>
                </div>
                <div class="card-body">
                    @if(Auth::user()->roles->count() > 0)
                        <div class="d-flex flex-wrap gap-2">
                            @foreach(Auth::user()->roles as $role)
                                <span class="badge bg-primary fs-6 px-3 py-2">
                                    <i class="bi bi-shield-fill me-1"></i>
                                    {{ $role->name }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">No roles assigned.</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-key-fill me-2 text-warning"></i> Your Permissions
                    </h3>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2">
                        Showing {{ Auth::user()->getAllPermissions()->take(10)->count() }} of {{ Auth::user()->getAllPermissions()->count() }} total permissions
                    </p>
                    @if(Auth::user()->getAllPermissions()->count() > 0)
                        <div class="d-flex flex-wrap gap-1">
                            @foreach(Auth::user()->getAllPermissions()->sortBy('module')->take(20) as $permission)
                                <span class="badge bg-light text-dark border" title="{{ $permission->description ?? 'No description' }}">
                                    {{ $permission->name }}
                                </span>
                            @endforeach
                            @if(Auth::user()->getAllPermissions()->count() > 20)
                                <span class="badge bg-secondary">+{{ Auth::user()->getAllPermissions()->count() - 20 }} more</span>
                            @endif
                        </div>
                    @else
                        <p class="text-muted mb-0">No direct permissions.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Access & Recent Records --}}
    <div class="row g-3 mb-3">
        {{-- Recent Employees --}}
        @can('employees.view')
        <div class="col-lg-8">
            <div class="card pk-recent-card h-100">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-person-badge me-1 text-success"></i> Recent Employees
                    </h3>
                    <a href="{{ route('employees.index') }}" class="btn btn-sm btn-outline-secondary">
                        View all
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Department</th>
                                    <th>Position</th>
                                    <th>Status</th>
                                    <th>Hired</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentEmployees as $employee)
                                    <tr>
                                        <td>
                                            <a href="{{ route('employees.show', $employee) }}" class="fw-semibold text-decoration-none">
                                                {{ $employee->name }}
                                            </a>
                                            <div class="text-xs text-muted">{{ $employee->email }}</div>
                                        </td>
                                        <td>{{ $employee->department }}</td>
                                        <td class="text-sm">{{ $employee->position }}</td>
                                        <td>
                                            @if($employee->status === 'active')
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-sm text-muted">{{ $employee->hire_date->format('M d, Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="bi bi-person-badge fs-2 d-block mb-2 opacity-25"></i>
                                            No employees yet
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endcan

        {{-- Quick Links --}}
        <div class="col-lg-{{ Auth::user()->can('employees.view') ? '4' : '12' }}">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-lightning-fill me-1 text-warning"></i> Quick Access
                    </h3>
                </div>
                <div class="card-body d-flex flex-column gap-2">
                    <a href="{{ route('profile.edit') }}" class="pk-quick-link">
                        <div class="pk-quick-icon" style="background:var(--pk-primary-light);color:var(--pk-primary)">
                            <i class="bi bi-person"></i>
                        </div>
                        <div>
                            <div class="fw-semibold">Edit Profile</div>
                            <div class="text-xs text-muted">Update your account info</div>
                        </div>
                    </a>

                    @can('users.view')
                        <a href="{{ route('users.index') }}" class="pk-quick-link">
                            <div class="pk-quick-icon" style="background:var(--pk-info-light);color:#0891b2">
                                <i class="bi bi-people"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">Manage Users</div>
                                <div class="text-xs text-muted">{{ $stats['users'] }} registered users</div>
                            </div>
                        </a>
                    @endcan

                    @can('roles.view')
                        <a href="{{ route('roles.index') }}" class="pk-quick-link">
                            <div class="pk-quick-icon" style="background:var(--pk-warning-light);color:#d97706">
                                <i class="bi bi-shield"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">Roles & Permissions</div>
                                <div class="text-xs text-muted">{{ $stats['roles'] }} roles, {{ $stats['permissions'] }} permissions</div>
                            </div>
                        </a>
                    @endcan

                    @can('employees.view')
                        <a href="{{ route('employees.create') }}" class="pk-quick-link">
                            <div class="pk-quick-icon" style="background:var(--pk-success-light);color:#16a34a">
                                <i class="bi bi-person-plus"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">Add Employee</div>
                                <div class="text-xs text-muted">Create a new employee record</div>
                            </div>
                        </a>
                    @endcan

                    @can('products.view')
                        <a href="{{ route('products.create') }}" class="pk-quick-link">
                            <div class="pk-quick-icon" style="background:var(--pk-purple-light);color:#7c3aed">
                                <i class="bi bi-plus-circle"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">Add Product</div>
                                <div class="text-xs text-muted">List a new product</div>
                            </div>
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Products --}}
    @can('products.view')
    <div class="row g-3">
        <div class="col-12">
            <div class="card pk-recent-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-box-seam me-1 text-info"></i> Recent Products
                    </h3>
                    <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-secondary">
                        View all
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>SKU</th>
                                    <th>Category</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-center">Stock</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentProducts as $product)
                                    <tr>
                                        <td>
                                            <a href="{{ route('products.show', $product) }}" class="fw-semibold text-decoration-none">
                                                {{ $product->name }}
                                            </a>
                                        </td>
                                        <td><code class="text-xs">{{ $product->sku }}</code></td>
                                        <td class="text-sm">{{ $product->category }}</td>
                                        <td class="text-end text-sm">${{ number_format((float) $product->price, 2) }}</td>
                                        <td class="text-center">
                                            @if($product->stock === 0)
                                                <span class="badge bg-danger">0</span>
                                            @elseif($product->stock < 10)
                                                <span class="badge bg-warning text-dark">{{ $product->stock }}</span>
                                            @else
                                                <span class="text-sm">{{ $product->stock }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($product->status === 'active')
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="bi bi-box-seam fs-2 d-block mb-2 opacity-25"></i>
                                            No products yet
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endcan
@endsection
