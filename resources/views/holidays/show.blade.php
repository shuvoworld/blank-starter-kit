@extends('layouts.form.app')

@section('header')
<h1 class="m-0">Holiday Details</h1>
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item"><a href="{{ route('holidays.index') }}">Holidays</a></li>
<li class="breadcrumb-item active" aria-current="page">{{ $holiday->name }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-calendar-event me-2"></i>
                    Holiday Information
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-6">
                        <table class="table table-bordered">
                            <tr>
                                <th width="160">Name</th>
                                <td>{{ $holiday->name }}</td>
                            </tr>
                            <tr>
                                <th>Date</th>
                                <td>{{ $holiday->date->format('l, F j, Y') }}</td>
                            </tr>
                            <tr>
                                <th>Type</th>
                                <td>
                                    @if($holiday->holiday_type === 'global')
                                        <span class="badge bg-primary">Global</span>
                                    @else
                                        <span class="badge bg-info">Regional</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Recurring</th>
                                <td>
                                    @if($holiday->is_recurring)
                                        <span class="badge bg-success"><i class="bi bi-arrow-repeat me-1"></i>Yes</span>
                                    @else
                                        <span class="text-muted">No</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    @if($holiday->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-sm-6">
                        <table class="table table-bordered">
                            @if($holiday->holiday_type === 'regional')
                                <tr>
                                    <th width="140">Location</th>
                                    <td>
                                        @if($holiday->country)
                                            {{ $holiday->country->name }}
                                        @endif
                                        @if($holiday->city)
                                            <br><small class="text-muted">{{ $holiday->city->name }}</small>
                                        @endif
                                        @if(!$holiday->country && !$holiday->city)
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @else
                                <tr>
                                    <th width="140">Location</th>
                                    <td><span class="text-muted">Applies to all</span></td>
                                </tr>
                            @endif
                            <tr>
                                <th>Sort Order</th>
                                <td>{{ $holiday->sort_order }}</td>
                            </tr>
                            <tr>
                                <th>Day of Week</th>
                                <td>{{ $holiday->date->format('l') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($holiday->notes)
                    <div class="mt-3">
                        <h6 class="text-primary">Notes</h6>
                        <p>{{ $holiday->notes }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        @include('layouts.form.includes.audits', ['element' => $holiday])
    </div>
</div>

@include('layouts.form.includes.action-buttons', ['element' => $holiday,'module' => 'holidays','moduleTitle'=>'Holiday'])
@endsection
