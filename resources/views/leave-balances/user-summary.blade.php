@extends('layouts.app')

@section('header')
<h1 class="m-0">Leave Summary: {{ $user->name }}</h1>
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item"><a href="{{ route('leave-balances.index') }}">Leave Balances</a></li>
<li class="breadcrumb-item active" aria-current="page">{{ $user->name }}</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title">
                <i class="bi bi-pie-chart me-2"></i>
                Leave Balance Summary - {{ $year }}
            </h3>
            <div>
                <select id="year-selector" class="form-select form-select-sm" style="width: auto;">
                    @foreach($availableYears as $y)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="bi bi-person me-2"></i>
            <strong>Employee:</strong> {{ $user->name }}
            <span class="mx-2">|</span>
            <strong>Email:</strong> {{ $user->email }}
        </div>

        @if($balances->isEmpty())
            <div class="alert alert-warning">
                <i class="bi bi-info-circle me-2"></i>
                No leave balances found for {{ $year }}.
            </div>
        @else
            <div class="row mb-4">
                @foreach($balances as $balance)
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card card-outline">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <span class="badge bg-info me-2">{{ $balance->leaveType->code }}</span>
                                    {{ $balance->leaveType->name }}
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Entitlement:</span>
                                        <strong>{{ $balance->total_entitlement }} days</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Taken:</span>
                                        <strong class="text-danger">{{ $balance->taken_days }} days</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Remaining:</span>
                                        <strong class="text-success">{{ $balance->remaining_days }} days</strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Pending:</span>
                                        <strong class="text-warning">{{ $balance->pending_days }} days</strong>
                                    </div>
                                </div>
                                <div class="progress" style="height: 25px;">
                                    @php
                                        $usagePercent = min($balance->usage_percentage, 100);
                                        $color = $usagePercent >= 80 ? 'bg-danger' : ($usagePercent >= 50 ? 'bg-warning' : 'bg-success');
                                    @endphp
                                    <div class="progress-bar {{ $color }}" role="progressbar"
                                        style="width: {{ $usagePercent }}%"
                                        aria-valuenow="{{ $usagePercent }}" aria-valuemin="0" aria-valuemax="100">
                                        {{ number_format($usagePercent, 1) }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <h5 class="mt-4 mb-3">
            <i class="bi bi-list-ul me-2"></i>
            All Requests ({{ $allRequests->count() }})
        </h5>
        @if($allRequests->isEmpty())
            <p class="text-muted">No leave requests found for {{ $year }}.</p>
        @else
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Leave Type</th>
                            <th>Date Range</th>
                            <th>Days</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Processed By</th>
                            <th>Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($allRequests as $request)
                            <tr>
                                <td>
                                    <span class="badge bg-info">{{ $request->leaveType->code }}</span>
                                    {{ $request->leaveType->name }}
                                </td>
                                <td>{{ $request->start_date->format('M d') }} - {{ $request->end_date->format('M d, Y') }}</td>
                                <td>{{ $request->total_days }}</td>
                                <td>{{ $request->reason ?? '—' }}</td>
                                <td>{!! $request->status_badge !!}</td>
                                <td>
                                    @if($request->approvedBy)
                                        {{ $request->approvedBy->name }}
                                    @elseif($request->rejectedBy)
                                        {{ $request->rejectedBy->name }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $request->created_at->format('M d, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<div class="mt-3">
    <a href="{{ route('leave-balances.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Leave Balances
    </a>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('year-selector').addEventListener('change', function() {
            const url = new URL(window.location);
            url.searchParams.set('year', this.value);
            window.location.href = url.toString();
        });
    });
</script>
@endpush
@endsection
