@extends('layouts.form.app')

@section('header')
<h1 class="m-0">Leave Balances</h1>
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item active" aria-current="page">Leave Balances</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <h3 class="card-title">
                <i class="bi bi-pie-chart me-2"></i>
                All Employee Leave Balances
            </h3>
            <div class="d-flex gap-2 align-items-center flex-wrap">
                <select id="year-filter" class="form-select form-select-sm" style="width: auto;">
                    <option value="">All Years</option>
                    @foreach($years as $year)
                        <option value="{{ $year }}" {{ $year == now()->year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
                <select id="leave-type-filter" class="form-select form-select-sm" style="width: auto;">
                    <option value="">All Leave Types</option>
                    @foreach($leaveTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
                <button type="button" class="btn btn-sm btn-secondary" id="reset-filters">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="leave-balances-table" class="table table-bordered table-hover table-striped w-100">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Employee</th>
                        <th>Leave Type</th>
                        <th>Entitlement</th>
                        <th>Usage</th>
                        <th>Pending</th>
                        <th>Remaining</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function initLeaveBalancesDataTable() {
        if (typeof $ !== 'undefined' && typeof bootstrap !== 'undefined') {
            $(document).ready(function() {
                var table = $('#leave-balances-table').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: {
                        url: '{{ route('leave-balances.index') }}',
                        type: 'GET',
                        data: function(d) {
                            d.year = $('#year-filter').val() || '';
                            d.leave_type_id = $('#leave-type-filter').val() || '';
                        }
                    },
                    columns: [
                        {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center'},
                        {data: 'employee_name', name: 'user.name'},
                        {data: 'leave_type', name: 'leaveType.name', orderable: false, searchable: false},
                        {data: 'entitlement', name: 'total_entitlement', orderable: true, searchable: false, className: 'text-center'},
                        {data: 'usage', name: 'taken_days', orderable: true, searchable: false},
                        {data: 'pending', name: 'pending_days', orderable: false, searchable: false, className: 'text-center'},
                        {data: 'remaining', name: 'remaining_days', orderable: true, searchable: false, className: 'text-center'},
                        {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center'}
                    ],
                    order: [[0, 'asc']],
                    pageLength: 25,
                    lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                    language: {
                        processing: '<div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>',
                        search: '_INPUT_',
                        searchPlaceholder: 'Search leave balances...',
                        lengthMenu: 'Show _MENU_',
                        info: 'Showing _START_ to _END_ of _TOTAL_ balances',
                        infoEmpty: 'No leave balances found',
                        infoFiltered: '(filtered from _MAX_ total)',
                        paginate: {
                            first: '<i class="bi bi-chevron-double-left"></i>',
                            previous: '<i class="bi bi-chevron-left"></i>',
                            next: '<i class="bi bi-chevron-right"></i>',
                            last: '<i class="bi bi-chevron-double-right"></i>'
                        },
                        emptyTable: '<div class="text-center py-4"><i class="bi bi-pie-chart fs-1 text-muted"></i><p class="text-muted mt-2">No leave balances found</p></div>'
                    },
                    dom: "<'pk-dt-top'lf><'pk-dt-table'rt><'pk-dt-foot'ip>",
                });

                // Filter handlers
                $('#year-filter, #leave-type-filter').on('change', function() {
                    table.ajax.reload();
                });

                $('#reset-filters').on('click', function() {
                    $('#year-filter').val('{{ now()->year }}');
                    $('#leave-type-filter').val('');
                    table.ajax.reload();
                });
            });
        } else {
            setTimeout(initLeaveBalancesDataTable, 100);
        }
    }

    initLeaveBalancesDataTable();
</script>
@endpush
@endsection
