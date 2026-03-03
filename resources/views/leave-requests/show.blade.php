@extends('layouts.form.app')

@section('header')
<h1 class="m-0">Leave Request Details</h1>
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item"><a href="{{ route('leave-requests.index') }}">Leave Requests</a></li>
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
                                <th width="160">Employee</th>
                                <td>
                                    {{ $leaveRequest->user->name }}
                                    <small class="text-muted d-block">{{ $leaveRequest->user->email }}</small>
                                </td>
                            </tr>
                            <tr>
                                <th>Leave Type</th>
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
                        <h6><i class="bi bi-check-circle me-2"></i>Approved By</h6>
                        <p class="mb-0">
                            This request was approved by <strong>{{ $leaveRequest->approvedBy->name }}</strong>
                            on {{ $leaveRequest->approved_at->format('M d, Y H:i') }}
                        </p>
                    </div>
                @endif

                @if($leaveRequest->status === 'rejected')
                    <div class="alert alert-danger">
                        <h6><i class="bi bi-x-circle me-2"></i>Rejected By</h6>
                        <p class="mb-0">
                            This request was rejected by <strong>{{ $leaveRequest->rejectedBy->name }}</strong>
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
    <a href="{{ route('leave-requests.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Leave Requests
    </a>
    @can('approve leave requests')
        @if($leaveRequest->isPending())
            <button type="button" class="btn btn-success btn-approve-request" data-url="{{ route('leave-requests.approve', $leaveRequest) }}">
                <i class="bi bi-check-lg me-1"></i> Approve
            </button>
            <button type="button" class="btn btn-danger btn-reject-request" data-url="{{ route('leave-requests.reject', $leaveRequest) }}">
                <i class="bi bi-x-lg me-1"></i> Reject
            </button>
        @endif
    @endcan
</div>

@can('approve leave requests')
    @if($leaveRequest->isPending())
        <!-- Approve Modal -->
        <div class="modal fade" id="approveModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">Approve Leave Request</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Approve leave request for <strong>{{ $leaveRequest->user->name }}</strong>?</p>
                        <p class="text-muted small mb-3">{{ $leaveRequest->total_days }} day{{ $leaveRequest->total_days > 1 ? 's' : '' }} from {{ $leaveRequest->start_date->format('M d, Y') }} to {{ $leaveRequest->end_date->format('M d, Y') }}</p>
                        <div class="mb-3">
                            <label for="approval_note" class="form-label">Add a note (optional)</label>
                            <textarea class="form-control" id="approval_note" rows="3" placeholder="Add any additional notes or comments..."></textarea>
                            <small class="text-muted">This note will be visible to the employee.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-success" id="confirmApprove">Approve</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div class="modal fade" id="rejectModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Reject Leave Request</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="rejectForm">
                        @csrf
                        <div class="modal-body">
                            <p class="text-muted mb-3">Provide a reason for rejecting this leave request:</p>
                            <div class="mb-0">
                                <label for="rejection_reason" class="form-label">Reason for Rejection <span class="text-muted">(optional)</span></label>
                                <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" placeholder="Explain why this request is being rejected..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Reject</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endcan

@push('scripts')
<script>
    @can('approve leave requests')
        @if($leaveRequest->isPending())
            document.addEventListener('DOMContentLoaded', function() {
                const approveModal = new bootstrap.Modal(document.getElementById('approveModal'));
                const rejectModal = new bootstrap.Modal(document.getElementById('rejectModal'));
                const approveUrl = document.querySelector('.btn-approve-request').dataset.url;
                const rejectUrl = document.querySelector('.btn-reject-request').dataset.url;

                document.querySelector('.btn-approve-request').addEventListener('click', function() {
                    approveModal.show();
                });

                document.querySelector('.btn-reject-request').addEventListener('click', function() {
                    rejectModal.show();
                });

                document.getElementById('confirmApprove').addEventListener('click', function() {
                    const btn = this;
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Approving...';

                    const approvalNote = document.getElementById('approval_note').value;

                    fetch(approveUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            approval_note: approvalNote
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        window.location.href = '{{ route('leave-requests.index') }}';
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred. Please try again.');
                        btn.disabled = false;
                        btn.innerHTML = 'Approve';
                    });
                });

                document.getElementById('rejectForm').addEventListener('submit', function(e) {
                    e.preventDefault();
                    const btn = this.querySelector('button[type="submit"]');
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Rejecting...';

                    const url = document.querySelector('.btn-reject-request').dataset.url;

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            rejection_reason: this.rejection_reason.value
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        window.location.href = '{{ route('leave-requests.index') }}';
                    })
                    .catch(error => {
                        alert('An error occurred. Please try again.');
                        btn.disabled = false;
                        btn.innerHTML = 'Reject';
                    });
                });
            });
        @endif
    @endcan
</script>
@endpush
@endsection
