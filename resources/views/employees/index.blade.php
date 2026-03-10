@extends('layouts.form.grid')

@section('content')
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body py-2">
                    <form id="filterForm" class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Department</label>
                            <select name="filter_department_id" class="form-select form-select-sm">
                                <option value="">All Departments</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Designation</label>
                            <select name="filter_designation_id" class="form-select form-select-sm">
                                <option value="">All Designations</option>
                                @foreach($designations as $designation)
                                    <option value="{{ $designation->id }}">{{ $designation->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1">Status</label>
                            <select name="filter_status" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1">Hire Date From</label>
                            <input type="date" name="filter_hire_date_from" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1">Hire Date To</label>
                            <input type="date" name="filter_hire_date_to" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-12 text-end">
                            <button type="button" id="clearFilters" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-x-lg me-1"></i> Clear Filters
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @parent
@endsection
