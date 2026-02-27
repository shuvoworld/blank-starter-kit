@extends('layouts.form.app')

@section('header')
<h1 class="m-0">Activity Detail</h1>
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('activity-log.index') }}">Activity Log</a></li>
        <li class="breadcrumb-item active" aria-current="page">#{{ $activity->id }}</li>
@endsection

@section('content')
<div class="row">
        {{-- Summary --}}
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-info-circle me-1"></i> Event Summary
                    </h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <th class="ps-3 text-muted" width="130">ID</th>
                            <td class="pe-3">{{ $activity->id }}</td>
                        </tr>
                        <tr>
                            <th class="ps-3 text-muted">Module</th>
                            <td class="pe-3">
                                @php
                                    $colours = ['user'=>'primary','employee'=>'success','product'=>'info','role'=>'warning','permission'=>'secondary'];
                                    $colour = $colours[$activity->log_name] ?? 'dark';
                                @endphp
                                <span class="badge bg-{{ $colour }}">{{ ucfirst($activity->log_name) }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th class="ps-3 text-muted">Event</th>
                            <td class="pe-3">
                                @php
                                    $evtColours = ['created'=>'success','updated'=>'primary','deleted'=>'danger'];
                                    $evtIcons   = ['created'=>'bi-plus-circle','updated'=>'bi-pencil','deleted'=>'bi-trash'];
                                    $evtColour  = $evtColours[$activity->event] ?? 'secondary';
                                    $evtIcon    = $evtIcons[$activity->event]   ?? 'bi-activity';
                                @endphp
                                <span class="badge bg-{{ $evtColour }}">
                                    <i class="bi {{ $evtIcon }} me-1"></i>{{ ucfirst($activity->event ?? '—') }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th class="ps-3 text-muted">Description</th>
                            <td class="pe-3">{{ $activity->description }}</td>
                        </tr>
                        <tr>
                            <th class="ps-3 text-muted">Subject</th>
                            <td class="pe-3">
                                @if($activity->subject_type)
                                    <span class="badge bg-light text-dark border">{{ class_basename($activity->subject_type) }}</span>
                                    #{{ $activity->subject_id }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="ps-3 text-muted">Performed by</th>
                            <td class="pe-3">
                                @if($activity->causer)
                                    <i class="bi bi-person-circle me-1 text-muted"></i>
                                    {{ $activity->causer->name ?? $activity->causer->email }}
                                @else
                                    <span class="text-muted">System</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="ps-3 text-muted">When</th>
                            <td class="pe-3">
                                {{ $activity->created_at->format('M d, Y H:i:s') }}
                                <span class="text-muted text-xs">({{ $activity->created_at->diffForHumans() }})</span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- Properties Diff --}}
        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-table me-1"></i>
                        @if($activity->event === 'created')
                            New Values
                        @elseif($activity->event === 'deleted')
                            Deleted Values
                        @else
                            Changes (Old → New)
                        @endif
                    </h3>
                </div>
                <div class="card-body p-0">
                    @php
                        $attributes = $activity->properties->get('attributes', []);
                        $old        = $activity->properties->get('old', []);
                        $allKeys    = collect(array_keys($attributes + $old))->sort()->values();
                    @endphp

                    @if($allKeys->isEmpty())
                        <div class="p-3 text-muted text-sm">No attribute data recorded.</div>
                    @else
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th width="160">Field</th>
                                    @if($activity->event === 'updated')
                                        <th>Old Value</th>
                                        <th>New Value</th>
                                    @else
                                        <th>Value</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($allKeys as $key)
                                    @php
                                        $newVal    = $attributes[$key] ?? null;
                                        $oldVal    = $old[$key] ?? null;
                                        $changed   = $activity->event === 'updated' && $oldVal !== $newVal;
                                    @endphp
                                    <tr class="{{ $changed ? 'table-warning' : '' }}">
                                        <td class="fw-semibold text-muted">{{ $key }}</td>
                                        @if($activity->event === 'updated')
                                            <td>
                                                @if($changed)
                                                    <span class="text-danger">
                                                        {{ is_null($oldVal) ? '—' : (is_bool($oldVal) ? ($oldVal ? 'true' : 'false') : $oldVal) }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">{{ is_null($oldVal) ? '—' : $oldVal }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($changed)
                                                    <span class="text-success fw-semibold">
                                                        {{ is_null($newVal) ? '—' : (is_bool($newVal) ? ($newVal ? 'true' : 'false') : $newVal) }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">{{ is_null($newVal) ? '—' : $newVal }}</span>
                                                @endif
                                            </td>
                                        @else
                                            <td>{{ is_null($newVal) ? '—' : (is_bool($newVal) ? ($newVal ? 'true' : 'false') : $newVal) }}</td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="mt-2">
        <a href="{{ route('activity-log.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Activity Log
        </a>
    </div>
@endsection
