@extends('layouts.app')

@section('header')
<h1 class="m-0">All Leave Requests</h1>
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item active" aria-current="page">Leave Requests</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <h3 class="card-title">
                <i class="bi bi-calendar-check me-2"></i>
                All Leave Requests
            </h3>
            <div class="d-flex gap-2 align-items-center flex-wrap">
                <select id="year-filter" class="form-select form-select-sm" style="width: auto;">
                    <option value="">All Years</option>
                    @foreach($years as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>
                <select id="status-filter" class="form-select form-select-sm" style="width: auto;">
                    <option value="all">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <button type="button" class="btn btn-sm btn-secondary" id="reset-filters">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="leave-requests-table" class="table table-bordered table-hover table-striped w-100">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Employee</th>
                        <th>Leave Type</th>
                        <th>Date Range</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th width="180">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="approveModalLabel">
                    <i class="bi bi-check-lg me-2"></i>
                    Approve Leave Request
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to approve the leave request for <strong id="approveEmployeeName"></strong>?</p>
                <p class="text-muted small mb-3">This will update their leave balance immediately.</p>
                <div class="mb-0">
                    <label for="approval_note" class="form-label">Add a note <span class="text-muted">(optional)</span></label>
                    <textarea class="form-control" id="approval_note" rows="3" placeholder="Add any additional notes or comments..."></textarea>
                    <small class="text-muted">This note will be visible to the employee.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-success" id="confirmApprove">
                    <i class="bi bi-check-lg me-1"></i> Approve
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="rejectModalLabel">
                    <i class="bi bi-x-lg me-2"></i>
                    Reject Leave Request
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="rejectForm">
                @csrf
                <div class="modal-body">
                    <p>Rejecting leave request for <strong id="rejectEmployeeName"></strong></p>
                    <div class="mb-0">
                        <label for="rejection_reason" class="form-label">Reason for Rejection <span class="text-muted">(optional)</span></label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" placeholder="Explain why this request is being rejected..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-lg me-1"></i> Reject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Cancel Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="cancelModalLabel">
                    <i class="bi bi-arrow-counterclockwise me-2"></i>
                    Cancel Leave Request
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel this <strong id="cancelLeaveName"></strong> request?</p>
                <p class="text-muted small mb-0">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i> No, Keep It
                </button>
                <button type="button" class="btn btn-warning" id="confirmCancel">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Yes, Cancel
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let table;
    let approveUrl = '';
    let rejectUrl = '';
    let cancelUrl = '';

    function initLeaveRequestsDataTable() {
        if (typeof $ !== 'undefined' && typeof bootstrap !== 'undefined') {
            $(document).ready(function() {
                table = $('#leave-requests-table').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: {
                        url: '{{ route('leave-requests.index') }}',
                        type: 'GET',
                        data: function(d) {
                            d.year = $('#year-filter').val() || '';
                            d.status = $('#status-filter').val() || 'all';
                        }
                    },
                    columns: [
                        {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center'},
                        {data: 'employee_name', name: 'user.name'},
                        {data: 'leave_type', name: 'leaveType.name', orderable: false, searchable: false},
                        {data: 'date_range', name: 'start_date', orderable: true},
                        {data: 'total_days', name: 'total_days', className: 'text-center'},
                        {data: 'status_badge', name: 'status', orderable: true, searchable: false, className: 'text-center'},
                        {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center'}
                    ],
                    order: [[5, 'desc']],
                    pageLength: 25,
                    lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                    language: {
                        processing: '<div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>',
                        search: '_INPUT_',
                        searchPlaceholder: 'Search leave requests...',
                        lengthMenu: 'Show _MENU_',
                        info: 'Showing _START_ to _END_ of _TOTAL_ leave requests',
                        infoEmpty: 'No leave requests found',
                        infoFiltered: '(filtered from _MAX_ total)',
                        paginate: {
                            first: '<i class="bi bi-chevron-double-left"></i>',
                            previous: '<i class="bi bi-chevron-left"></i>',
                            next: '<i class="bi bi-chevron-right"></i>',
                            last: '<i class="bi bi-chevron-double-right"></i>'
                        },
                        emptyTable: '<div class="text-center py-4"><i class="bi bi-calendar-check fs-1 text-muted"></i><p class="text-muted mt-2">No leave requests found</p></div>'
                    },
                    dom: "<'pk-dt-top'lf><'pk-dt-table'rt><'pk-dt-foot'ip>",
                });

                var approveModal = new bootstrap.Modal(document.getElementById('approveModal'));
                var rejectModal = new bootstrap.Modal(document.getElementById('rejectModal'));
                var cancelModal = new bootstrap.Modal(document.getElementById('cancelModal'));

                $(document).on('click', '.btn-approve', function(e) {
                    e.preventDefault();
                    approveUrl = $(this).data('url');
                    $('#approveEmployeeName').text($(this).data('name'));
                    approveModal.show();
                });

                $(document).on('click', '.btn-reject', function(e) {
                    e.preventDefault();
                    rejectUrl = $(this).data('url');
                    $('#rejectEmployeeName').text($(this).data('name'));
                    rejectModal.show();
                });

                $(document).on('click', '.btn-cancel', function(e) {
                    e.preventDefault();
                    cancelUrl = $(this).data('url');
                    $('#cancelLeaveName').text($(this).data('name'));
                    cancelModal.show();
                });

                $('#confirmApprove').on('click', function() {
                    var btn = $(this);
                    var originalHtml = btn.html();
                    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Approving...');

                    var approvalNote = $('#approval_note').val();

                    $.ajax({
                        url: approveUrl,
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        data: JSON.stringify({
                            approval_note: approvalNote
                        }),
                        success: function(response) {
                            approveModal.hide();
                            $('#approval_note').val('');
                            table.ajax.reload(null, false);
                            showToast('success', 'Leave request approved successfully. New balance: ' + response.new_balance + ' days.');
                        },
                        error: function(xhr) {
                            console.error('Approval error:', xhr);
                            console.error('Response:', xhr.responseText);
                            var message = 'An error occurred while approving the request.';
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                message = xhr.responseJSON.error;
                            } else if (xhr.responseText) {
                                try {
                                    var errorData = JSON.parse(xhr.responseText);
                                    if (errorData.error) message = errorData.error;
                                    if (errorData.message) message = errorData.message;
                                } catch (e) {
                                    message = 'Server error. Please try again.';
                                }
                            }
                            approveModal.hide();
                            showToast('danger', message);
                            btn.prop('disabled', false).html(originalHtml);
                        },
                        complete: function() {
                            btn.prop('disabled', false).html(originalHtml);
                        }
                    });
                });

                $('#rejectForm').on('submit', function(e) {
                    e.preventDefault();

                    var btn = $(this).find('button[type="submit"]');
                    var originalHtml = btn.html();
                    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Rejecting...');

                    $.ajax({
                        url: rejectUrl,
                        type: 'POST',
                        headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                        data: $(this).serialize(),
                        success: function(response) {
                            rejectModal.hide();
                            $('#rejectForm')[0].reset();
                            table.ajax.reload(null, false);
                            showToast('success', 'Leave request rejected successfully.');
                        },
                        error: function(xhr) {
                            var errors = xhr.responseJSON?.errors || {};
                            if (errors.rejection_reason) {
                                $('#rejection_reason').addClass('is-invalid');
                                $('#rejection_reason').next('.invalid-feedback').remove();
                                $('#rejection_reason').after('<div class="invalid-feedback">' + errors.rejection_reason + '</div>');
                            } else {
                                rejectModal.hide();
                                var message = xhr.responseJSON?.error || 'An error occurred.';
                                showToast('danger', message);
                            }
                        },
                        complete: function() {
                            btn.prop('disabled', false).html(originalHtml);
                        }
                    });
                });

                $('#confirmCancel').on('click', function() {
                    var btn = $(this);
                    var originalHtml = btn.html();
                    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Cancelling...');

                    $.ajax({
                        url: cancelUrl,
                        type: 'POST',
                        headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                        success: function(response) {
                            cancelModal.hide();
                            table.ajax.reload(null, false);
                            showToast('success', 'Leave request cancelled successfully.');
                        },
                        error: function(xhr) {
                            cancelModal.hide();
                            var message = xhr.responseJSON?.error || 'An error occurred.';
                            showToast('danger', message);
                        },
                        complete: function() {
                            btn.prop('disabled', false).html(originalHtml);
                        }
                    });
                });

                // Filter handlers
                $('#year-filter, #status-filter').on('change', function() {
                    table.ajax.reload();
                });

                $('#reset-filters').on('click', function() {
                    $('#year-filter').val('');
                    $('#status-filter').val('all');
                    table.ajax.reload();
                });

                function showToast(type, message) {
                    var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                    var icon = type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill';
                    var toastContainer = $('#toast-container');
                    if (toastContainer.length === 0) {
                        toastContainer = $('<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055"></div>');
                        $('body').append(toastContainer);
                    }
                    var toast = $(
                        '<div class="toast align-items-center ' + alertClass + ' border-0" role="alert">' +
                        '<div class="d-flex">' +
                        '<div class="toast-body"><i class="bi ' + icon + ' me-2"></i>' + message + '</div>' +
                        '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>' +
                        '</div></div>'
                    );
                    toastContainer.append(toast);
                    var bsToast = new bootstrap.Toast(toast[0], {delay: 5000});
                    bsToast.show();
                    toast.on('hidden.bs.toast', function() { $(this).remove(); });
                }
            });
        } else {
            setTimeout(initLeaveRequestsDataTable, 100);
        }
    }

    initLeaveRequestsDataTable();
</script>
@endpush
@endsection
