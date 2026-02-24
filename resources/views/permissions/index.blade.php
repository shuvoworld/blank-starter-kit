@extends('layouts.app')

@section('header')
<h1 class="m-0">Permissions</h1>
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Permissions</li>
@endsection

@section('content')
<div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">
                    <i class="bi bi-key me-2"></i>
                    All Permissions
                </h3>
                @can('create permissions')
                    <a href="{{ route('permissions.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Create Permission
                    </a>
                @endcan
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="permissions-table" class="table table-bordered table-hover table-striped w-100">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Module</th>
                        <th>Description</th>
                        <th>Roles</th>
                        <th width="120">Actions</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
    @push('scripts')
        <script>
            function initPermissionsDataTable() {
                if (typeof $ !== 'undefined') {
                    $(document).ready(function () {
                        $('#permissions-table').DataTable({
                            processing: true,
                            serverSide: true,
                            responsive: true,
                            ajax: {
                                url: '{{ route('permissions.index') }}',
                                type: 'GET'
                            },
                            columns: [
                                {data: 'name', name: 'name'},
                                {data: 'module', name: 'module'},
                                {data: 'description', name: 'description'},
                                {data: 'roles_count', name: 'roles_count'},
                                {
                                    data: 'action',
                                    name: 'action',
                                    orderable: false,
                                    searchable: false,
                                    className: 'text-center'
                                }
                            ],
                            order: [[0, 'asc']],
                            pageLength: 25,
                            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                            language: {
                                processing: '<div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>',
                                search: '_INPUT_',
                                searchPlaceholder: 'Search permissions...',
                                lengthMenu: 'Show _MENU_',
                                info: 'Showing _START_ to _END_ of _TOTAL_ permissions',
                                infoEmpty: 'No permissions found',
                                infoFiltered: '(filtered from _MAX_ total)',
                                paginate: {
                                    first: '<i class="bi bi-chevron-double-left"></i>',
                                    previous: '<i class="bi bi-chevron-left"></i>',
                                    next: '<i class="bi bi-chevron-right"></i>',
                                    last: '<i class="bi bi-chevron-double-right"></i>'
                                },
                                emptyTable: '<div class="text-center py-4"><i class="bi bi-key fs-1 text-muted"></i><p class="text-muted mt-2">No permissions found</p></div>'
                            },
                            dom: "<'pk-dt-top'lf><'pk-dt-table'rt><'pk-dt-foot'ip>",
                        });
                    });
                } else {
                    setTimeout(initPermissionsDataTable, 100);
                }
            }

            initPermissionsDataTable();
        </script>
    @endpush
@endsection
