@extends('layouts.form.app')

@section('header')
    <h1 class="m-0">{{ $editing ? 'Edit Leave Type' : 'Add Leave Type' }}</h1>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('leave-types.index') }}">Leave Types</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $editing ? 'Edit' : 'Create' }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-{{ $editing ? 'pencil' : 'calendar-check' }} me-2"></i>
                        {{ $editing ? 'Edit Leave Type: ' . $record->name : 'New Leave Type' }}
                    </h3>
                </div>
                <form action="{{ $editing ? route('leave-types.update', $record) : route('leave-types.store') }}"
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
                                'placeholder' => 'e.g., Sick Leave',
                                'div'         => 'col-md-6',
                                'required'    => true,
                                'autofocus'   => true,
                            ]])

                            @include('layouts.form.inputs.text', ['var' => [
                                'name'        => 'code',
                                'label'       => 'Code',
                                'value'       => $record?->code,
                                'placeholder' => 'e.g., SL',
                                'div'         => 'col-md-6',
                                'required'    => true,
                                'params'      => ['style' => 'text-transform:uppercase'],
                            ]])
                        </div>

                        <div class="row">
                            @include('layouts.form.inputs.textarea', ['var' => [
                                'name'        => 'description',
                                'label'       => 'Description',
                                'value'       => $record?->description,
                                'placeholder' => 'Leave type description',
                                'rows'        => 3,
                                'div'         => 'col-md-12',
                            ]])
                        </div>

                        <div class="row">
                            @include('layouts.form.inputs.select', ['var' => [
                                'name'     => 'is_paid',
                                'label'    => 'Is Paid',
                                'value'    => $record?->is_paid ?? 1,
                                'options'  => [1 => 'Yes', 0 => 'No'],
                                'div'      => 'col-md-4',
                                'select2' => true,
                                'required' => true,
                            ]])

                            @include('layouts.form.inputs.select', ['var' => [
                                'name'     => 'requires_approval',
                                'label'    => 'Requires Approval',
                                'value'    => $record?->requires_approval ?? 1,
                                'options'  => [1 => 'Yes', 0 => 'No'],
                                'div'      => 'col-md-4',
                                'required' => true,
                                'select2'  => true,
                            ]])

                            @include('layouts.form.inputs.select', ['var' => [
                                'name'    => 'requires_document',
                                'label'   => 'Requires Document',
                                'value'   => $record?->requires_document ?? 0,
                                'options' => [0 => 'No', 1 => 'Yes'],
                                'div'     => 'col-md-4',
                                'select2' => true,
                            ]])
                        </div>

                        <div class="row">
                            @include('layouts.form.inputs.text', ['var' => [
                                'name'        => 'max_days_per_year',
                                'label'       => 'Max Days/Year',
                                'type'        => 'number',
                                'value'       => $record?->max_days_per_year,
                                'placeholder' => 'e.g., 12',
                                'div'         => 'col-md-4',
                                'params'      => ['min' => '0'],
                            ]])

                            @include('layouts.form.inputs.text', ['var' => [
                                'name'        => 'max_days_per_month',
                                'label'       => 'Max Days/Month',
                                'type'        => 'number',
                                'value'       => $record?->max_days_per_month,
                                'placeholder' => 'e.g., 2',
                                'div'         => 'col-md-4',
                                'params'      => ['min' => '0'],
                            ]])

                            @include('layouts.form.inputs.text', ['var' => [
                                'name'        => 'max_consecutive_days',
                                'label'       => 'Max Consecutive Days',
                                'type'        => 'number',
                                'value'       => $record?->max_consecutive_days,
                                'placeholder' => 'e.g., 5',
                                'div'         => 'col-md-4',
                                'params'      => ['min' => '0'],
                            ]])
                        </div>

                        <div class="row">
                            @include('layouts.form.inputs.select', ['var' => [
                                'name'    => 'carry_forward',
                                'label'   => 'Carry Forward',
                                'value'   => $record?->carry_forward ?? 0,
                                'options' => [0 => 'No', 1 => 'Yes'],
                                'div'     => 'col-md-4',
                                'select2' => true,
                            ]])

                            @include('layouts.form.inputs.text', ['var' => [
                                'name'        => 'carry_forward_limit',
                                'label'       => 'Carry Forward Limit',
                                'type'        => 'number',
                                'value'       => $record?->carry_forward_limit,
                                'placeholder' => 'e.g., 5',
                                'div'         => 'col-md-4',
                                'params'      => ['min' => '0'],
                            ]])

                            @include('layouts.form.inputs.text', ['var' => [
                                'name'        => 'carry_forward_expiry_days',
                                'label'       => 'Expiry Days',
                                'type'        => 'number',
                                'value'       => $record?->carry_forward_expiry_days,
                                'placeholder' => 'e.g., 90',
                                'div'         => 'col-md-4',
                                'tooltip'     => 'Days after which carried leave expires',
                                'params'      => ['min' => '0'],
                            ]])
                        </div>

                        <div class="row">
                            @include('layouts.form.inputs.text', ['var' => [
                                'name'        => 'min_days_before_request',
                                'label'       => 'Min Notice Days',
                                'type'        => 'number',
                                'value'       => $record?->min_days_before_request,
                                'placeholder' => 'e.g., 3',
                                'div'         => 'col-md-4',
                                'tooltip'     => 'Days advance notice required',
                                'params'      => ['min' => '0'],
                            ]])

                            @include('layouts.form.inputs.select', ['var' => [
                                'name'    => 'is_gender_specific',
                                'label'   => 'Gender Specific',
                                'value'   => $record?->is_gender_specific ?? 0,
                                'options' => [0 => 'No', 1 => 'Yes'],
                                'div'     => 'col-md-4',
                                'select2' => true,
                            ]])

                            @include('layouts.form.inputs.select', ['var' => [
                                'name'       => 'applicable_gender',
                                'label'      => 'Applicable Gender',
                                'value'      => $record?->applicable_gender,
                                'prompt'     => 'Not Applicable',
                                'options'    => ['male' => 'Male', 'female' => 'Female', 'other' => 'Other'],
                                'div'        => 'col-md-4',
                                'select2'    => true,
                                'allow_clear' => true,
                            ]])
                        </div>

                        <div class="row">
                            @include('layouts.form.inputs.select', ['var' => [
                                'name'    => 'is_paid_pro_rata',
                                'label'   => 'Pro-rata Calculation',
                                'value'   => $record?->is_paid_pro_rata ?? 0,
                                'options' => [0 => 'No', 1 => 'Yes'],
                                'div'     => 'col-md-6',
                                'tooltip' => 'For mid-year joiners',
                                'select2' => true,
                            ]])

                            @include('layouts.form.inputs.text', ['var' => [
                                'name'        => 'sort_order',
                                'label'       => 'Sort Order',
                                'type'        => 'number',
                                'value'       => $record?->sort_order ?? 0,
                                'placeholder' => '0',
                                'div'         => 'col-md-3',
                                'params'      => ['min' => '0'],
                            ]])

                            @include('layouts.form.inputs.select', ['var' => [
                                'name'     => 'is_active',
                                'label'    => 'Status',
                                'value'    => $record?->is_active ?? 1,
                                'options'  => [1 => 'Active', 0 => 'Inactive'],
                                'div'      => 'col-md-3',
                                'required' => true,
                                'select2'  => true,
                            ]])
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>
                            {{ $editing ? 'Update' : 'Create' }} Leave Type
                        </button>
                        <a href="{{ route('leave-types.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-lg me-1"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-4">
            @if($editing)
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="bi bi-info-circle me-2"></i>
                            Leave Type Metadata
                        </h3>
                    </div>
                    @include('leave-types.includes.record-info', ['leaveType' => $record])
                </div>

                @can('delete leave types')
                    <div class="card card-danger card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Danger Zone
                            </h3>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small">Once deleted, this leave type record cannot be recovered.</p>
                            <form action="{{ route('leave-types.destroy', $record) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Are you sure you want to delete {{ $record->name }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="bi bi-trash me-1"></i> Delete Leave Type
                                </button>
                            </form>
                        </div>
                    </div>
                @endcan
            @else
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="bi bi-info-circle me-2"></i>
                            Information
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small"><i class="bi bi-dot"></i> Fields marked with <span class="text-danger">*</span> are required.</p>
                        <p class="text-muted small"><i class="bi bi-dot"></i> Code must be unique and short (e.g., SL, AL).</p>
                        <p class="text-muted small"><i class="bi bi-dot"></i> Max days per year/month limit leave allocation.</p>
                        <p class="text-muted small"><i class="bi bi-dot"></i> Carry forward allows unused leave to next year.</p>
                        <p class="text-muted small mb-0"><i class="bi bi-dot"></i> Only active leave types will be available for selection.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.getElementById('code').addEventListener('input', function () {
                    this.value = this.value.toUpperCase();
                });
            });
        </script>
    @endpush
@endsection
