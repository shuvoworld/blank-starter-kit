@extends('layouts.form.app')

@section('header')
    <h1 class="m-0">Email Logs</h1>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active" aria-current="page">Email Logs</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h3 class="card-title">
                    <i class="bi bi-envelope me-2"></i>
                    All Email Logs
                </h3>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <select id="filterStatus" class="form-select form-select-sm" style="width:auto">
                        <option value="">All statuses</option>
                        <option value="sent">Sent</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="email-logs-table" class="table table-bordered table-hover table-striped w-100">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th>Subject</th>
                            <th>Recipients</th>
                            <th>Mailable</th>
                            <th width="100">Status</th>
                            <th>Sent At</th>
                            <th width="70">Details</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function initEmailLogsTable() {
                if (typeof $ !== 'undefined' && typeof bootstrap !== 'undefined') {
                    $(document).ready(function () {
                        var table = $('#email-logs-table').DataTable({
                            processing: true,
                            serverSide: true,
                            responsive: true,
                            ajax: {
                                url: '{{ route('email-logs.index') }}',
                                type: 'GET',
                                data: function (d) {
                                    d.status = $('#filterStatus').val();
                                }
                            },
                            columns: [
                                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center'},
                                {data: 'subject', name: 'subject'},
                                {data: 'recipients', name: 'to', orderable: false, searchable: false},
                                {data: 'mailable_short', name: 'mailable_class', orderable: false},
                                {data: 'status_badge', name: 'status', searchable: false},
                                {data: 'sent_at_formatted', name: 'sent_at'},
                                {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center'},
                            ],
                            order: [[0, 'desc']],
                            pageLength: 25,
                            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                            language: {
                                processing: '<div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>',
                                search: '_INPUT_',
                                searchPlaceholder: 'Search logs...',
                                lengthMenu: 'Show _MENU_',
                                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                                infoEmpty: 'No email logs found',
                                infoFiltered: '(filtered from _MAX_ total)',
                                paginate: {
                                    first: '<i class="bi bi-chevron-double-left"></i>',
                                    previous: '<i class="bi bi-chevron-left"></i>',
                                    next: '<i class="bi bi-chevron-right"></i>',
                                    last: '<i class="bi bi-chevron-double-right"></i>'
                                },
                                emptyTable: '<div class="text-center py-4"><i class="bi bi-envelope fs-1 text-muted"></i><p class="text-muted mt-2">No email logs recorded yet</p></div>'
                            },
                            dom: "<'pk-dt-top'lf><'pk-dt-table'rt><'pk-dt-foot'ip>",
                        });

                        $('#filterStatus').on('change', function () {
                            table.ajax.reload();
                        });
                    });
                } else {
                    setTimeout(initEmailLogsTable, 100);
                }
            }

            initEmailLogsTable();
        </script>
    @endpush
@endsection
