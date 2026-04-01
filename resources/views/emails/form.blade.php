@extends('layouts.form.app')

@section('header')
    <h1 class="m-0">{{ $editing ? 'Edit Email' : 'Add Email' }}</h1>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('emails.index') }}">Emails</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $editing ? 'Edit' : 'Create' }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-{{ $editing ? 'pencil' : 'grid' }} me-2"></i>
                        {{ $editing ? 'Edit Email: ' . $record->name : 'New Email' }}
                    </h3>
                </div>
                <form action="{{ $editing ? route('emails.update', $record) : route('emails.store') }}"
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

                            @include('layouts.form.inputs.text', ['var' => [
                                'name'        => 'subject',
                                'label'       => 'Subject',
                                'value'       => $record?->subject,
                                'placeholder' => 'Subject',
                                'div'         => 'col-md-6',
                                'required'    => true,
                                'autofocus'   => true,
                            ]])
                            @include('layouts.form.inputs.text', ['var' => [
                                'name'        => 'to',
                                'label'       => 'To',
                                'value'       => $record?->to,
                                'placeholder' => 'To',
                                'div'         => 'col-md-6',
                                'required'    => true,
                                'autofocus'   => true,
                            ]])
                            @include('layouts.form.inputs.text', ['var' => [
                                'name'        => 'cc',
                                'label'       => 'CC',
                                'value'       => $record?->cc,
                                'placeholder' => 'CC',
                                'div'         => 'col-md-6',
                                'required'    => false,
                                'autofocus'   => true,
                            ]])
                            @include('layouts.form.inputs.text', ['var' => [
                                'name'        => 'bcc',
                                'label'       => 'BCC',
                                'value'       => $record?->subject,
                                'placeholder' => 'BCC',
                                'div'         => 'col-md-6',
                                'required'    => false,
                                'autofocus'   => true,
                            ]])
                            @include('layouts.form.inputs.text', ['var' => [
                                'name'        => 'status',
                                'label'       => 'Status',
                                'value'       => $record?->status,
                                'placeholder' => 'Status',
                                'div'         => 'col-md-6',
                                'required'    => false,
                                'autofocus'   => true,
                            ]]) @include('layouts.form.inputs.text', ['var' => [
                                'name'        => 'successfully_delivered_at',
                                'label'       => 'Delivered At',
                                'value'       => $record?->successfully_delivered_at,
                                'placeholder' => 'Delivered At',
                                'div'         => 'col-md-6',
                                'required'    => false,
                                'autofocus'   => true,
                            ]])
                        </div>
                    </div>

                    @include('layouts.form.includes.footer', ['element' => $record,'module' => 'emails','moduleTitle'=>'Email'])
                </form>
            </div>
        </div>

        <div class="col-lg-4">
            @if($editing)
                @include('layouts.form.includes.audits', ['element' => $record])
                @include('layouts.form.includes.delete-warning', ['element' => $record,'module' => 'emails','moduleTitle'=>'Email'])
            @else
                @include('layouts.form.includes.information', ['element' => $record,'module' => 'emails','moduleTitle'=>'Email'])
            @endif

        </div>
    </div>
@endsection
@push('scripts')
    @parent
    <script>
        // $(document).ready(function () {
        //     $('#to').select2({
        //         tags: true,                 // allow new tags
        //         tokenSeparators: [','],     // press comma to add
        //         placeholder: "Add tags",
        //         width: '100%'
        //     });
        // });
    </script>
@endpush