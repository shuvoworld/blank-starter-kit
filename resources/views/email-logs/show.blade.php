@extends('layouts.form.app')

@section('header')
    <h1 class="m-0">Email Log Detail</h1>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('email-logs.index') }}">Email Logs</a></li>
    <li class="breadcrumb-item active" aria-current="page">#{{ $emailLog->id }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-info-circle me-1"></i> Email Summary
                    </h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <th class="ps-3 text-muted" width="130">ID</th>
                            <td class="pe-3">{{ $emailLog->id }}</td>
                        </tr>
                        <tr>
                            <th class="ps-3 text-muted">Status</th>
                            <td class="pe-3">
                                @if($emailLog->status === 'sent')
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Sent</span>
                                @else
                                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Failed</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="ps-3 text-muted">Subject</th>
                            <td class="pe-3">{{ $emailLog->subject }}</td>
                        </tr>
                        <tr>
                            <th class="ps-3 text-muted">To</th>
                            <td class="pe-3">
                                @foreach($emailLog->to as $address)
                                    <span class="badge bg-light text-dark border me-1">{{ $address }}</span>
                                @endforeach
                            </td>
                        </tr>
                        @if($emailLog->cc)
                            <tr>
                                <th class="ps-3 text-muted">CC</th>
                                <td class="pe-3">
                                    @foreach($emailLog->cc as $address)
                                        <span class="badge bg-light text-dark border me-1">{{ $address }}</span>
                                    @endforeach
                                </td>
                            </tr>
                        @endif
                        @if($emailLog->bcc)
                            <tr>
                                <th class="ps-3 text-muted">BCC</th>
                                <td class="pe-3">
                                    @foreach($emailLog->bcc as $address)
                                        <span class="badge bg-light text-dark border me-1">{{ $address }}</span>
                                    @endforeach
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <th class="ps-3 text-muted">Mailable</th>
                            <td class="pe-3">
                                @if($emailLog->mailable_class)
                                    <span class="text-muted small" title="{{ $emailLog->mailable_class }}">{{ class_basename($emailLog->mailable_class) }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="ps-3 text-muted">Sent At</th>
                            <td class="pe-3">
                                @if($emailLog->sent_at)
                                    {{ $emailLog->sent_at->format('M d, Y H:i:s') }}
                                    <span class="text-muted small">({{ $emailLog->sent_at->diffForHumans() }})</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        @if($emailLog->failed_reason)
                            <tr>
                                <th class="ps-3 text-muted">Failure Reason</th>
                                <td class="pe-3 text-danger small">{{ $emailLog->failed_reason }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-file-earmark-text me-1"></i> Email Body
                    </h3>
                </div>
                <div class="card-body p-0">
                    @if($emailLog->body)
                        <iframe
                            srcdoc="{{ $emailLog->body }}"
                            class="w-100 border-0"
                            style="min-height: 400px"
                            sandbox="allow-same-origin"
                        ></iframe>
                    @else
                        <div class="p-3 text-muted">No body content recorded.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('email-logs.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Email Logs
        </a>
    </div>
@endsection
