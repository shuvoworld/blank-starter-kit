@extends('layouts.form.app')

@section('header')
<h1 class="m-0">My Leave Request Details</h1>
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item"><a href="{{ route('my-leave-requests.index') }}">My Leave Requests</a></li>
<li class="breadcrumb-item active" aria-current="page">Request #{{ $leaveRequest->id }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-calendar-check me-2"></i>
                    Leave Request Information
                </h3>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-6">
                        <table class="table table-bordered">
                            <tr>
                                <th width="160">Leave Type</th>
                                <td>
                                    <span class="badge bg-info">{{ $leaveRequest->leaveType->code }}</span>
                                    {{ $leaveRequest->leaveType->name }}
                                </td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>{!! $leaveRequest->status_badge !!}</td>
                            </tr>
                            <tr>
                                <th>Year</th>
                                <td>{{ $leaveRequest->year }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-sm-6">
                        <table class="table table-bordered">
                            <tr>
                                <th width="160">Start Date</th>
                                <td>{{ $leaveRequest->start_date->format('l, F j, Y') }}</td>
                            </tr>
                            <tr>
                                <th>End Date</th>
                                <td>{{ $leaveRequest->end_date->format('l, F j, Y') }}</td>
                            </tr>
                            <tr>
                                <th>Total Days</th>
                                <td>
                                    <strong>{{ $leaveRequest->total_days }}</strong> day{{ $leaveRequest->total_days > 1 ? 's' : '' }}
                                    <small class="text-muted d-block">(excluding weekends & holidays)</small>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($leaveRequest->reason)
                    <div class="mb-3">
                        <h6 class="text-primary">Reason</h6>
                        <p>{{ $leaveRequest->reason }}</p>
                    </div>
                @endif

                @if($leaveRequest->rejection_reason)
                    <div class="alert alert-danger">
                        <h6><i class="bi bi-x-circle me-2"></i>Rejection Reason</h6>
                        <p class="mb-0">{{ $leaveRequest->rejection_reason }}</p>
                    </div>
                @endif

                @if($leaveRequest->approval_note)
                    <div class="alert alert-success">
                        <h6><i class="bi bi-check-circle me-2"></i>Approval Note</h6>
                        <p class="mb-0">{{ $leaveRequest->approval_note }}</p>
                    </div>
                @endif

                @if($leaveRequest->status === 'approved')
                    <div class="alert alert-success">
                        <h6><i class="bi bi-check-circle me-2"></i>Approved</h6>
                        <p class="mb-0">
                            Your request was approved by <strong>{{ $leaveRequest->approvedBy->name }}</strong>
                            on {{ $leaveRequest->approved_at->format('M d, Y H:i') }}
                        </p>
                    </div>
                @endif

                @if($leaveRequest->status === 'rejected')
                    <div class="alert alert-danger">
                        <h6><i class="bi bi-x-circle me-2"></i>Rejected</h6>
                        <p class="mb-0">
                            Your request was rejected by <strong>{{ $leaveRequest->rejectedBy->name }}</strong>
                            on {{ $leaveRequest->rejected_at->format('M d, Y H:i') }}
                        </p>
                    </div>
                @endif

                @if($leaveRequest->status === 'cancelled')
                    <div class="alert alert-warning">
                        <h6><i class="bi bi-arrow-counterclockwise me-2"></i>Cancelled</h6>
                        <p class="mb-0">
                            This request was cancelled on {{ $leaveRequest->cancelled_at->format('M d, Y H:i') }}
                        </p>
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
                    Request Timeline
                </h3>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Request ID:</td>
                        <td>#{{ $leaveRequest->id }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Submitted:</td>
                        <td>{{ $leaveRequest->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    @if($leaveRequest->approved_at)
                        <tr>
                            <td class="text-muted">Approved:</td>
                            <td>{{ $leaveRequest->approved_at->format('M d, Y H:i') }}</td>
                        </tr>
                    @endif
                    @if($leaveRequest->rejected_at)
                        <tr>
                            <td class="text-muted">Rejected:</td>
                            <td>{{ $leaveRequest->rejected_at->format('M d, Y H:i') }}</td>
                        </tr>
                    @endif
                    @if($leaveRequest->cancelled_at)
                        <tr>
                            <td class="text-muted">Cancelled:</td>
                            <td>{{ $leaveRequest->cancelled_at->format('M d, Y H:i') }}</td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

<div class="mt-3">
    <a href="{{ route('my-leave-requests.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to My Requests
    </a>
    @if($leaveRequest->canBeCancelled())
        <button type="button" class="btn btn-warning btn-cancel-request" data-url="{{ route('my-leave-requests.cancel', $leaveRequest) }}">
            <i class="bi bi-arrow-counterclockwise me-1"></i> Cancel Request
        </button>
    @endif
</div>

@if($leaveRequest->canBeCancelled())
    <!-- Cancel Modal -->
    <div class="modal fade" id="cancelModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">Cancel Leave Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to cancel this <strong>{{ $leaveRequest->leaveType->name }}</strong> request?</p>
                    <p class="text-muted small mb-0">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, Keep It</button>
                    <button type="button" class="btn btn-warning" id="confirmCancel">Yes, Cancel</button>
                </div>
            </div>
        </div>
    </div>
@endif

@push('scripts')
<script>
    @if($leaveRequest->canBeCancelled())
        document.addEventListener('DOMContentLoaded', function() {
            const cancelModal = new bootstrap.Modal(document.getElementById('cancelModal'));

            document.querySelector('.btn-cancel-request').addEventListener('click', function() {
                cancelModal.show();
            });

            document.getElementById('confirmCancel').addEventListener('click', function() {
                const btn = this;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Cancelling...';

                fetch(btn.closest('.btn-cancel-request').dataset.url, {
                    method: 'POST',
                    headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                    body: JSON.stringify({})
                })
                .then(response => response.json())
                .then(data => {
                    window.location.href = '{{ route('my-leave-requests.index') }}';
                })
                .catch(error => {
                    alert('An error occurred. Please try again.');
                    btn.disabled = false;
                    btn.innerHTML = 'Yes, Cancel';
                });
            });
        });
    @endif
</script>
@endpush
@endsection
