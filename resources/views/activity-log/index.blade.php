@extends('layouts.app')

@section('header')
<h1 class="m-0">Activity Log</h1>
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Activity Log</li>
@endsection

@section('content')
<div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h3 class="card-title">
                    <i class="bi bi-activity me-2"></i>
                    All Activity
                </h3>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <select id="filterLogName" class="form-select form-select-sm" style="width:auto">
                        <option value="">All modules</option>
                        @foreach($logNames as $logName)
                            <option value="{{ $logName }}">{{ ucfirst($logName) }}</option>
                        @endforeach
                    </select>
                    <select id="filterEvent" class="form-select form-select-sm" style="width:auto">
                        <option value="">All events</option>
                        <option value="created">Created</option>
                        <option value="updated">Updated</option>
                        <option value="deleted">Deleted</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="activity-table" class="table table-bordered table-hover table-striped w-100">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th>Module</th>
                            <th>Event</th>
                            <th>Subject</th>
                            <th>Description</th>
                            <th>By</th>
                            <th>When</th>
                            <th width="70">Details</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function initActivityTable() {
            if (typeof $ !== 'undefined' && typeof bootstrap !== 'undefined') {
                $(document).ready(function () {
                    var table = $('#activity-table').DataTable({
                        processing: true,
                        serverSide: true,
                        responsive: true,
                        ajax: {
                            url: '{{ route('activity-log.index') }}',
                            type: 'GET',
                            data: function (d) {
                                d.log_name = $('#filterLogName').val();
                                d.event    = $('#filterEvent').val();
                            }
                        },
                        columns: [
                            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center'},
                            {data: 'log_name_badge', name: 'log_name', searchable: true},
                            {data: 'event_badge', name: 'event', orderable: false, searchable: false},
                            {data: 'subject_info', name: 'subject_type', orderable: false, searchable: false},
                            {data: 'description', name: 'description'},
                            {data: 'causer_name', name: 'causer_id', orderable: false, searchable: false},
                            {data: 'created_at_formatted', name: 'created_at'},
                            {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center'},
                        ],
                        order: [[6, 'desc']],
                        pageLength: 25,
                        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                        language: {
                            processing: '<div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>',
                            search: '_INPUT_',
                            searchPlaceholder: 'Search logs...',
                            lengthMenu: 'Show _MENU_',
                            info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                            infoEmpty: 'No activity found',
                            infoFiltered: '(filtered from _MAX_ total)',
                            paginate: {
                                first: '<i class="bi bi-chevron-double-left"></i>',
                                previous: '<i class="bi bi-chevron-left"></i>',
                                next: '<i class="bi bi-chevron-right"></i>',
                                last: '<i class="bi bi-chevron-double-right"></i>'
                            },
                            emptyTable: '<div class="text-center py-4"><i class="bi bi-activity fs-1 text-muted"></i><p class="text-muted mt-2">No activity recorded yet</p></div>'
                        },
                        dom: "<'pk-dt-top'lf><'pk-dt-table'rt><'pk-dt-foot'ip>",
                    });

                    // Re-fetch when filters change
                    $('#filterLogName, #filterEvent').on('change', function () {
                        table.ajax.reload();
                    });
                });
            } else {
                setTimeout(initActivityTable, 100);
            }
        }

        initActivityTable();
    </script>
    @endpush
@endsection
