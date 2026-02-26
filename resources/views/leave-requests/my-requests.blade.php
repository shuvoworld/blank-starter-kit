@extends('layouts.app')

@section('header')
<h1 class="m-0">My Leave Requests</h1>
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item active" aria-current="page">My Leave Requests</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <h3 class="card-title">
                <i class="bi bi-calendar-check me-2"></i>
                My Leave Requests
            </h3>
            <div class="d-flex gap-2 align-items-center flex-wrap">
                <a href="{{ route('my-leave-requests.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> New Request
                </a>
                <select id="year-filter" class="form-select form-select-sm" style="width: auto;">
                    <option value="">All Years</option>
                    @foreach($availableYears as $year)
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
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Quick Tip:</strong> You can cancel pending requests before they are approved. Once approved, requests cannot be cancelled without admin intervention.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <div class="table-responsive">
            <table id="my-leave-requests-table" class="table table-bordered table-hover table-striped w-100">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Leave Type</th>
                        <th>Date Range</th>
                        <th>Duration</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
            </table>
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
    let cancelUrl = '';

    function initMyLeaveRequestsDataTable() {
        if (typeof $ !== 'undefined' && typeof bootstrap !== 'undefined') {
            $(document).ready(function() {
                table = $('#my-leave-requests-table').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: {
                        url: '{{ route('my-leave-requests.index') }}',
                        type: 'GET',
                        data: function(d) {
                            d.year = $('#year-filter').val() || '';
                            d.status = $('#status-filter').val() || 'all';
                        }
                    },
                    columns: [
                        {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center'},
                        {data: 'leave_type', name: 'leaveType.name', orderable: false, searchable: false},
                        {data: 'date_range', name: 'start_date', orderable: true},
                        {data: 'total_days', name: 'total_days', className: 'text-center'},
                        {data: 'reason', name: 'reason'},
                        {data: 'status_badge', name: 'status', orderable: true, searchable: false, className: 'text-center'},
                        {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center'}
                    ],
                    order: [[5, 'desc']],
                    pageLength: 10,
                    lengthMenu: [[10, 25, 50], [10, 25, 50]],
                    language: {
                        processing: '<div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>',
                        search: '_INPUT_',
                        searchPlaceholder: 'Search my leave requests...',
                        lengthMenu: 'Show _MENU_',
                        info: 'Showing _START_ to _END_ of _TOTAL_ requests',
                        infoEmpty: 'No leave requests found',
                        infoFiltered: '(filtered from _MAX_ total)',
                        paginate: {
                            first: '<i class="bi bi-chevron-double-left"></i>',
                            previous: '<i class="bi bi-chevron-left"></i>',
                            next: '<i class="bi bi-chevron-right"></i>',
                            last: '<i class="bi bi-chevron-double-right"></i>'
                        },
                        emptyTable: '<div class="text-center py-4"><i class="bi bi-calendar-check fs-1 text-muted"></i><p class="text-muted mt-2">No leave requests found</p><p><a href="{{ route('my-leave-requests.create') }}" class="btn btn-primary btn-sm">Create Your First Request</a></p></div>'
                    },
                    dom: "<'pk-dt-top'lf><'pk-dt-table'rt><'pk-dt-foot'ip>",
                });

                var cancelModal = new bootstrap.Modal(document.getElementById('cancelModal'));

                $(document).on('click', '.btn-cancel', function(e) {
                    e.preventDefault();
                    cancelUrl = $(this).data('url');
                    $('#cancelLeaveName').text($(this).data('name'));
                    cancelModal.show();
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
            setTimeout(initMyLeaveRequestsDataTable, 100);
        }
    }

    initMyLeaveRequestsDataTable();
</script>
@endpush
@endsection
