@extends('layouts.form.app')

@section('header')
    <h1 class="m-0">Employee Category Details</h1>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('employee-categories.index') }}">Employee Categories</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $employeeCategory->name }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-grid me-2"></i>
                        Employee Category Information
                    </h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="160">Name</th>
                            <td>{{ $employeeCategory->name }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            @include('layouts.form.includes.audits', ['element' => $employeeCategory])
        </div>
    </div>

    @include('layouts.form.includes.action-buttons', ['element' => $employeeCategory,'module' => 'employee-categories','moduleTitle'=>'Employee Category'])
@endsection
