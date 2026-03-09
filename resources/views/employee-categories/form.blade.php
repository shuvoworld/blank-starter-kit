@extends('layouts.form.app')

@section('header')
    <h1 class="m-0">{{ $editing ? 'Edit Employee Category' : 'Add Employee Category' }}</h1>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('employee-categories.index') }}">Employee Categories</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $editing ? 'Edit' : 'Create' }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-{{ $editing ? 'pencil' : 'grid' }} me-2"></i>
                        {{ $editing ? 'Edit Employee Category: ' . $record->name : 'New Employee Category' }}
                    </h3>
                </div>
                <form action="{{ $editing ? route('employee-categories.update', $record) : route('employee-categories.store') }}"
                      method="POST">
                    @csrf
                    @if($editing)
                        @method('PUT')
                    @endif

                    <div class="card-body">
                        <div class="row">
                            @include('layouts.form.inputs.text', ['var' => [
                                'name'        => 'name',
                                'label'       => 'Name',
                                'value'       => $record?->name,
                                'placeholder' => 'Enter name',
                                'div'         => 'col-md-12',
                                'required'    => true,
                                'autofocus'   => true,
                            ]])
                        </div>
                    </div>

                    @include('layouts.form.includes.footer', ['element' => $record,'module' => 'employee-categories','moduleTitle'=>'Employee Category'])
                </form>
            </div>
        </div>

        <div class="col-lg-4">

            @if($editing)
                @include('layouts.form.includes.audits', ['element' => $record])
                @include('layouts.form.includes.delete-warning', ['element' => $record,'module' => 'employee-categories','moduleTitle'=>'Employee Category'])
            @else
                @include('layouts.form.includes.information', ['element' => $record,'module' => 'employee-categories','moduleTitle'=>'Employee Category'])
            @endif

        </div>
    </div>
@endsection
