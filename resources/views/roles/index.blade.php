@extends('layouts.form.app')

@section('header')
    <h1 class="m-0">Roles</h1>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active" aria-current="page">Roles</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">
                    <i class="bi bi-shield me-2"></i>
                    All Roles
                </h3>
                @can('create roles')
                    <a href="{{ route('roles.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Create Role
                    </a>
                @endcan
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="roles-table" class="table table-bordered table-hover table-striped w-100">
                    <thead>
                        <tr>
                            @foreach($tableColumns as $column)
                                <th @isset($column['width']) width="{{ $column['width'] }}" @endisset>
                                    {{ $column['label'] }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            var dtColumns = @json($dtColumns);

            function initRolesDataTable() {
                if (typeof $ !== 'undefined' && typeof bootstrap !== 'undefined') {
                    $(document).ready(function () {
                        $('#roles-table').DataTable({
                            processing: true,
                            serverSide: true,
                            responsive: true,
                            ajax: {
                                url: '{{ route('roles.datatable') }}',
                                type: 'GET'
                            },
                            columns: dtColumns,
                            order: [[0, 'asc']],
                            pageLength: 10,
                            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                            language: {
                                processing: '<div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>',
                                search: '_INPUT_',
                                searchPlaceholder: 'Search roles...',
                                lengthMenu: 'Show _MENU_',
                                info: 'Showing _START_ to _END_ of _TOTAL_ roles',
                                infoEmpty: 'No roles found',
                                infoFiltered: '(filtered from _MAX_ total)',
                                paginate: {
                                    first: '<i class="bi bi-chevron-double-left"></i>',
                                    previous: '<i class="bi bi-chevron-left"></i>',
                                    next: '<i class="bi bi-chevron-right"></i>',
                                    last: '<i class="bi bi-chevron-double-right"></i>'
                                },
                                emptyTable: '<div class="text-center py-4"><i class="bi bi-shield fs-1 text-muted"></i><p class="text-muted mt-2">No roles found</p></div>'
                            },
                            dom: "<'pk-dt-top'lf><'pk-dt-table'rt><'pk-dt-foot'ip>",
                        });
                    });
                } else {
                    setTimeout(initRolesDataTable, 100);
                }
            }

            initRolesDataTable();
        </script>
    @endpush
@endsection
