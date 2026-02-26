@extends('layouts.app')

@section('header')
<h1 class="m-0">My Leave Summary</h1>
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item active" aria-current="page">My Leave Summary</li>
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
        @if($balances->isEmpty())
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                No leave balances found for {{ $year }}. Your balances will be created when you submit your first leave request.
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
            <i class="bi bi-clock-history me-2"></i>
            Pending Requests ({{ $pendingRequests->count() }})
        </h5>
        @if($pendingRequests->isEmpty())
            <p class="text-muted">No pending requests.</p>
        @else
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>Leave Type</th>
                            <th>Date Range</th>
                            <th>Days</th>
                            <th>Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingRequests as $request)
                            <tr>
                                <td>
                                    <span class="badge bg-info">{{ $request->leaveType->code }}</span>
                                    {{ $request->leaveType->name }}
                                </td>
                                <td>{{ $request->start_date->format('M d') }} - {{ $request->end_date->format('M d, Y') }}</td>
                                <td>{{ $request->total_days }}</td>
                                <td>{{ $request->created_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <h5 class="mt-4 mb-3">
            <i class="bi bi-check-circle me-2"></i>
            Approved Requests ({{ $approvedRequests->count() }})
        </h5>
        @if($approvedRequests->isEmpty())
            <p class="text-muted">No approved requests for {{ $year }}.</p>
        @else
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>Leave Type</th>
                            <th>Date Range</th>
                            <th>Days</th>
                            <th>Approved On</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($approvedRequests as $request)
                            <tr>
                                <td>
                                    <span class="badge bg-info">{{ $request->leaveType->code }}</span>
                                    {{ $request->leaveType->name }}
                                </td>
                                <td>{{ $request->start_date->format('M d') }} - {{ $request->end_date->format('M d, Y') }}</td>
                                <td>{{ $request->total_days }}</td>
                                <td>{{ $request->approved_at->format('M d, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('year-selector').addEventListener('change', function() {
            window.location.href = '{{ route('my-leave-summary') }}?year=' + this.value;
        });
    });
</script>
@endpush
@endsection
