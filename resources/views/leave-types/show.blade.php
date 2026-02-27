@extends('layouts.form.app')

@section('header')
<h1 class="m-0">Leave Type Details</h1>
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item"><a href="{{ route('leave-types.index') }}">Leave Types</a></li>
<li class="breadcrumb-item active" aria-current="page">{{ $leaveType->name }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-calendar-check me-2"></i>
                    Leave Type Information
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-6">
                        <table class="table table-bordered">
                            <tr>
                                <th width="160">Name</th>
                                <td>{{ $leaveType->name }}</td>
                            </tr>
                            <tr>
                                <th>Code</th>
                                <td><span class="badge bg-info">{{ $leaveType->code }}</span></td>
                            </tr>
                            <tr>
                                <th>Is Paid</th>
                                <td>
                                    @if($leaveType->is_paid)
                                        <span class="badge bg-success">Yes</span>
                                    @else
                                        <span class="badge bg-warning">No</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Requires Approval</th>
                                <td>
                                    @if($leaveType->requires_approval)
                                        <span class="badge bg-success">Yes</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Requires Document</th>
                                <td>
                                    @if($leaveType->requires_document)
                                        <span class="badge bg-success">Yes</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-sm-6">
                        <table class="table table-bordered">
                            <tr>
                                <th width="180">Max Days/Year</th>
                                <td>{{ $leaveType->max_days_per_year ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Max Days/Month</th>
                                <td>{{ $leaveType->max_days_per_month ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Max Consecutive Days</th>
                                <td>{{ $leaveType->max_consecutive_days ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Min Notice Days</th>
                                <td>{{ $leaveType->min_days_before_request ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    @if($leaveType->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($leaveType->description || $leaveType->carry_forward || $leaveType->is_gender_specific)
                    <div class="mt-3">
                        <h6 class="text-primary">Additional Settings</h6>
                        @if($leaveType->description)
                            <p><strong>Description:</strong> {{ $leaveType->description }}</p>
                        @endif

                        @if($leaveType->carry_forward)
                            <p>
                                <strong>Carry Forward:</strong> <span class="badge bg-success">Enabled</span>
                                @if($leaveType->carry_forward_limit)
                                    <span class="text-muted">(Max: {{ $leaveType->carry_forward_limit }} days)</span>
                                @endif
                                @if($leaveType->carry_forward_expiry_days)
                                    <span class="text-muted">(Expires after {{ $leaveType->carry_forward_expiry_days }} days)</span>
                                @endif
                            </p>
                        @else
                            <p><strong>Carry Forward:</strong> <span class="text-muted">Disabled</span></p>
                        @endif

                        @if($leaveType->is_gender_specific)
                            <p>
                                <strong>Gender Specific:</strong> <span class="badge bg-info">{{ ucfirst($leaveType->applicable_gender) }}</span>
                            </p>
                        @endif

                        @if($leaveType->is_paid_pro_rata)
                            <p><strong>Pro-rata Calculation:</strong> <span class="badge bg-info">Enabled</span> (for mid-year joiners)</p>
                        @endif
                    </div>
                @endif
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
                        <td>{{ $leaveType->id }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Sort Order:</td>
                        <td>{{ $leaveType->sort_order }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Created:</td>
                        <td>{{ $leaveType->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Updated:</td>
                        <td>{{ $leaveType->updated_at->format('M d, Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="mt-3">
    <a href="{{ route('leave-types.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Leave Types
    </a>
    @can('update leave types')
        <a href="{{ route('leave-types.edit', $leaveType) }}" class="btn btn-primary">
            <i class="bi bi-pencil me-1"></i> Edit Leave Type
        </a>
    @endcan
</div>
@endsection
