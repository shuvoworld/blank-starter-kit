@extends('layouts.form.app')

@section('header')
<h1 class="m-0">Designations</h1>
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Designations</li>
@endsection

@section('content')
<!-- Filters -->
<div class="row mb-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body py-2">
                <form id="filterForm" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Status</label>
                        <select name="filter_status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-9 text-end">
                        <button type="button" id="clearFilters" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i> Clear Filters
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">
                    <i class="bi bi-person-badge me-2"></i>
                    All Designations
                </h3>
                @can('create designations')
                    <a href="{{ route('designations.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Add Designation
                    </a>
                @endcan
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="designations-table" class="table table-bordered table-hover table-striped w-100">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th width="100">Sort Order</th>
                            <th width="180">Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteModalLabel">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Confirm Delete
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong id="deleteItemName"></strong>?</p>
                    <p class="text-muted small mb-0">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function initDesignationsDataTable() {
            if (typeof $ !== 'undefined' && typeof bootstrap !== 'undefined') {
                $(document).ready(function() {
                    var table = $('#designations-table').DataTable({
                        processing: true,
                        serverSide: true,
                        responsive: true,
                        ajax: {
                            url: '{{ route('designations.index') }}',
                            type: 'GET',
                            data: function(d) {
                                d.filter_status = $('select[name="filter_status"]').val();
                            }
                        },
                        columns: [
                            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center'},
                            {data: 'name', name: 'name'},
                            {data: 'description', name: 'description'},
                            {data: 'status_badge', name: 'is_active', orderable: false, searchable: false, className: 'text-center'},
                            {data: 'sort_order', name: 'sort_order', className: 'text-center'},
                            {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center'}
                        ],
                        order: [[1, 'asc']],
                        pageLength: 10,
                        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                        language: {
                            processing: '<div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>',
                            search: '_INPUT_',
                            searchPlaceholder: 'Search designations...',
                            lengthMenu: 'Show _MENU_',
                            info: 'Showing _START_ to _END_ of _TOTAL_ designations',
                            infoEmpty: 'No designations found',
                            infoFiltered: '(filtered from _MAX_ total)',
                            paginate: {
                                first: '<i class="bi bi-chevron-double-left"></i>',
                                previous: '<i class="bi bi-chevron-left"></i>',
                                next: '<i class="bi bi-chevron-right"></i>',
                                last: '<i class="bi bi-chevron-double-right"></i>'
                            },
                            emptyTable: '<div class="text-center py-4"><i class="bi bi-person-badge fs-1 text-muted"></i><p class="text-muted mt-2">No designations found</p></div>'
                        },
                        dom: "<'pk-dt-top'lf><'pk-dt-table'rt><'pk-dt-foot'ip>",
                    });

                    // Reload table on filter change
                    $('#filterForm select, #filterForm input').on('change', function() {
                        table.ajax.reload();
                    });

                    // Clear filters
                    $('#clearFilters').on('click', function() {
                        $('#filterForm')[0].reset();
                        table.ajax.reload();
                    });

                    var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
                    var deleteUrl = '';

                    $(document).on('click', '.btn-delete', function(e) {
                        e.preventDefault();
                        deleteUrl = $(this).data('url');
                        $('#deleteItemName').text($(this).data('name'));
                        deleteModal.show();
                    });

                    $('#confirmDelete').on('click', function() {
                        var btn = $(this);
                        var originalHtml = btn.html();
                        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Deleting...');

                        $.ajax({
                            url: deleteUrl,
                            type: 'DELETE',
                            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                            success: function() {
                                deleteModal.hide();
                                table.ajax.reload(null, false);
                                showToast('success', 'Designation deleted successfully.');
                            },
                            error: function(xhr) {
                                deleteModal.hide();
                                var message = xhr.responseJSON?.error || 'An error occurred.';
                                showToast('danger', message);
                            },
                            complete: function() {
                                btn.prop('disabled', false).html(originalHtml);
                            }
                        });
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
                setTimeout(initDesignationsDataTable, 100);
            }
        }

        initDesignationsDataTable();
    </script>
    @endpush
@endsection
