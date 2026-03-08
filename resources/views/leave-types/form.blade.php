@use('Illuminate\Support\Facades\Storage')
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
                      method="POST" enctype="multipart/form-data">
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
                            @include('layouts.form.inputs.file', ['var' => [
                                'name'    => 'supporting_document',
                                'label'   => 'Supporting Document',
                                'accept'  => '.pdf,.doc,.docx,.jpg,.jpeg,.png',
                                'div'     => 'col-md-12',
                                'tooltip' => 'Upload a sample/template document (PDF, Word, or image). Max 5MB.',
                                'preview' => $record?->supporting_document ? Storage::disk('public')->url($record->supporting_document) : null,
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

                    @include('layouts.form.includes.footer', ['element' => $record,'module' => 'leave-types','moduleTitle'=>'Leave Type'])
                </form>
            </div>
        </div>

        <div class="col-lg-4">
            @if($editing)
                @include('layouts.form.includes.audits', ['element' => $record])
                @include('layouts.form.includes.delete-warning', ['element' => $record,'module' => 'leave-types','moduleTitle'=>'Leave Type'])
            @else
                @include('layouts.form.includes.information', ['element' => $record,'module' => 'leave-types','moduleTitle'=>'Leave Type'])
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
