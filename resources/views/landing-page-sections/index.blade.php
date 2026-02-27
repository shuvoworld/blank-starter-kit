@extends('layouts.form.app')

@section('header')
<h1 class="m-0">Landing Page Customization</h1>
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item active" aria-current="page">Landing Page</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title">
                <i class="bi bi-palette me-2"></i> Landing Page Sections
            </h3>
            <a href="/" target="_blank" class="btn btn-outline-primary">
                <i class="bi bi-eye me-1"></i> Preview Page
            </a>
        </div>
    </div>
    <div class="card-body">
        {{-- Section Filter Tabs --}}
        <ul class="nav nav-pills mb-4" id="sectionTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="all-tab" data-bs-toggle="pill" data-bs-target="#all" type="button" role="tab">
                    <i class="bi bi-list-ul me-1"></i> All Sections
                </button>
            </li>
            @foreach([
                'general' => 'General',
                'hero' => 'Hero',
                'features' => 'Features',
                'stats' => 'Stats',
                'about' => 'About',
                'pricing' => 'Pricing',
                'cta' => 'CTA',
                'contact' => 'Contact',
                'footer' => 'Footer'
            ] as $key => $label)
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="{{ $key }}-tab" data-bs-toggle="pill" data-bs-target="#{{ $key }}" type="button" role="tab">
                        {{ $label }}
                    </button>
                </li>
            @endforeach
        </ul>

        <div class="table-responsive">
            <table id="landing-page-sections-table" class="table table-bordered table-hover table-striped w-100">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Section</th>
                        <th>Label</th>
                        <th>Key</th>
                        <th>Type</th>
                        <th>Value</th>
                        <th>Status</th>
                        <th>Order</th>
                        <th width="100">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Quick Edit Modal -->
<div class="modal fade" id="quickEditModal" tabindex="-1" aria-labelledby="quickEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="quickEditModalLabel">
                    <i class="bi bi-pencil me-2"></i> Edit Section
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickEditForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" name="key" id="edit-key">
                    <input type="hidden" name="section" id="edit-section">
                    <input type="hidden" name="type" id="edit-type">
                    <input type="hidden" name="sort_order" id="edit-sort-order">

                    <div class="mb-3">
                        <label class="form-label">Label</label>
                        <input type="text" class="form-control" name="label" id="edit-label" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Value</label>
                        <textarea class="form-control" name="value" id="edit-value" rows="5"></textarea>
                        <div class="form-text">Type: <span id="edit-type-display"></span></div>
                    </div>

                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="edit-is-active" value="1" checked>
                        <label class="form-check-label" for="edit-is-active">
                            Active
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function initLandingPageSectionsDataTable() {
        if (typeof $ !== 'undefined' && typeof bootstrap !== 'undefined') {
            $(document).ready(function() {
                var currentSection = 'all';

                var table = $('#landing-page-sections-table').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: {
                        url: '{{ route('landing-page-sections.index') }}',
                        type: 'GET',
                        data: function(d) {
                            d.section_filter = currentSection;
                        }
                    },
                    columns: [
                        {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center'},
                        {data: 'section_badge', name: 'section', orderable: false, searchable: false, className: 'text-center'},
                        {data: 'label', name: 'label'},
                        {data: 'key', name: 'key'},
                        {data: 'type_badge', name: 'type', orderable: false, searchable: false, className: 'text-center'},
                        {data: 'preview', name: 'value', orderable: false, searchable: false},
                        {data: 'status_badge', name: 'is_active', orderable: false, searchable: false, className: 'text-center'},
                        {data: 'sort_order', name: 'sort_order', className: 'text-center'},
                        {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center'}
                    ],
                    order: [[7, 'asc']],
                    pageLength: 25,
                    lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                    language: {
                        processing: '<div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>',
                        search: '_INPUT_',
                        searchPlaceholder: 'Search sections...',
                        lengthMenu: 'Show _MENU_',
                        info: 'Showing _START_ to _END_ of _TOTAL_ sections',
                        infoEmpty: 'No sections found',
                        infoFiltered: '(filtered from _MAX_ total)',
                        paginate: {
                            first: '<i class="bi bi-chevron-double-left"></i>',
                            previous: '<i class="bi bi-chevron-left"></i>',
                            next: '<i class="bi bi-chevron-right"></i>',
                            last: '<i class="bi bi-chevron-double-right"></i>'
                        },
                        emptyTable: '<div class="text-center py-4"><i class="bi bi-palette fs-1 text-muted"></i><p class="text-muted mt-2">No sections found</p></div>'
                    },
                    dom: "<'pk-dt-top'lf><'pk-dt-table'rt><'pk-dt-foot'ip>",
                });

                // Section filter tabs
                $('#sectionTabs button[data-bs-toggle="pill"]').on('shown.bs.tab', function(e) {
                    currentSection = $(e.target).attr('id').replace('-tab', '');
                    table.ajax.reload();
                });

                // Quick edit modal
                $(document).on('click', '.btn-edit-section', function(e) {
                    e.preventDefault();
                    var editUrl = $(this).data('url');

                    $.get(editUrl, function(data) {
                        $('#edit-key').val(data.key);
                        $('#edit-section').val(data.section);
                        $('#edit-type').val(data.type);
                        $('#edit-sort-order').val(data.sort_order);
                        $('#edit-label').val(data.label);
                        $('#edit-value').val(data.value);
                        $('#edit-is-active').prop('checked', data.is_active);
                        $('#edit-type-display').text(data.type);

                        $('#quickEditForm').attr('action', editUrl);
                        $('#quickEditModal').modal('show');
                    });
                });

                // Form submission
                $('#quickEditForm').on('submit', function(e) {
                    e.preventDefault();
                    var form = $(this);
                    var url = form.attr('action');
                    var btn = form.find('button[type="submit"]');
                    var originalHtml = btn.html();

                    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

                    $.ajax({
                        url: url,
                        type: 'PUT',
                        data: form.serialize(),
                        headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                        success: function() {
                            $('#quickEditModal').modal('hide');
                            table.ajax.reload(null, false);
                            showToast('success', 'Section updated successfully.');
                        },
                        error: function(xhr) {
                            var message = xhr.responseJSON?.message || 'An error occurred.';
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
            setTimeout(initLandingPageSectionsDataTable, 100);
        }
    }

    initLandingPageSectionsDataTable();
</script>
@endpush
