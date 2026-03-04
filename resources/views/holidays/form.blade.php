@extends('layouts.form.app')

@section('header')
    <h1 class="m-0">{{ $editing ? 'Edit Holiday' : 'Add Holiday' }}</h1>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('holidays.index') }}">Holidays</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $editing ? 'Edit' : 'Create' }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-{{ $editing ? 'pencil' : 'calendar-event' }} me-2"></i>
                        {{ $editing ? 'Edit Holiday: ' . $record->name : 'New Holiday' }}
                    </h3>
                </div>
                <form action="{{ $editing ? route('holidays.update', $record) : route('holidays.store') }}"
                      method="POST">
                    @csrf
                    @if($editing) @method('PUT') @endif

                    <div class="card-body">
                        <div class="row">
                            @include('layouts.form.inputs.text', ['var' => [
                                'name'        => 'name',
                                'label'       => 'Holiday Name',
                                'value'       => $record?->name,
                                'placeholder' => "e.g., New Year's Day",
                                'div'         => 'col-md-6',
                                'required'    => true,
                                'autofocus'   => true,
                            ]])

                            @include('layouts.form.inputs.text', ['var' => [
                                'name'     => 'date',
                                'label'    => 'Date',
                                'type'     => 'date',
                                'value'    => $record?->date?->format('Y-m-d'),
                                'div'      => 'col-md-6',
                                'required' => true,
                            ]])
                        </div>

                        <div class="row">
                            @include('layouts.form.inputs.select', ['var' => [
                                'name'        => 'holiday_type',
                                'label'       => 'Holiday Type',
                                'value'       => $record?->holiday_type ?? 'global',
                                'options'     => ['global' => 'Global', 'regional' => 'Regional'],
                                'div'         => 'col-md-4',
                                'required'    => true,
                                'select2'     => true,
                                'attributes'  => 'id="holiday_type"',
                            ]])

                            <div class="col-md-4 mb-3" id="country-field" style="display: none;">
                                <label for="country_id" class="form-label">Country</label>
                                <select class="form-select" id="country_id" name="country_id" data-toggle="select2" data-placeholder="Select Country">
                                    <option value=""></option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->id }}" {{ $record?->country_id == $country->id ? 'selected' : '' }}>
                                            {{ $country->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-3" id="city-field" style="display: none;">
                                <label for="city_id" class="form-label">City</label>
                                <select class="form-select" id="city_id" name="city_id" data-toggle="select2" data-placeholder="Select City">
                                    <option value=""></option>
                                    @foreach($cities as $city)
                                        <option value="{{ $city->id }}" {{ $record?->city_id == $city->id ? 'selected' : '' }}>
                                            {{ $city->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            @include('layouts.form.inputs.textarea', ['var' => [
                                'name'        => 'notes',
                                'label'       => 'Notes',
                                'value'       => $record?->notes,
                                'placeholder' => 'Additional notes about this holiday',
                                'rows'        => 3,
                                'div'         => 'col-md-12',
                            ]])
                        </div>

                        <div class="row">
                            @include('layouts.form.inputs.select', ['var' => [
                                'name'    => 'is_recurring',
                                'label'   => 'Recurring Yearly',
                                'value'   => $record?->is_recurring ?? 0,
                                'options' => [0 => 'No', 1 => 'Yes'],
                                'div'     => 'col-md-4',
                                'select2' => true,
                            ]])

                            @include('layouts.form.inputs.text', ['var' => [
                                'name'        => 'sort_order',
                                'label'       => 'Sort Order',
                                'type'        => 'number',
                                'value'       => $record?->sort_order ?? 0,
                                'placeholder' => '0',
                                'div'         => 'col-md-4',
                                'params'      => ['min' => '0'],
                            ]])

                            @include('layouts.form.inputs.select', ['var' => [
                                'name'     => 'is_active',
                                'label'    => 'Status',
                                'value'    => $record?->is_active ?? 1,
                                'options'  => [1 => 'Active', 0 => 'Inactive'],
                                'div'      => 'col-md-4',
                                'required' => true,
                                'select2'  => true,
                            ]])
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>
                            {{ $editing ? 'Update' : 'Create' }} Holiday
                        </button>
                        <a href="{{ route('holidays.index') }}" class="btn btn-secondary">
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
                            Holiday Metadata
                        </h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="text-muted">ID:</td>
                                <td>{{ $record->id }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Created:</td>
                                <td>{{ $record->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Updated:</td>
                                <td>{{ $record->updated_at->format('M d, Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                @can('delete holidays')
                    <div class="card card-danger card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Danger Zone
                            </h3>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small">Once deleted, this holiday record cannot be recovered.</p>
                            <form action="{{ route('holidays.destroy', $record) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Are you sure you want to delete {{ $record->name }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="bi bi-trash me-1"></i> Delete Holiday
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
                        <p class="text-muted small"><i class="bi bi-dot"></i> <strong>Global holidays</strong> apply to all employees.</p>
                        <p class="text-muted small"><i class="bi bi-dot"></i> <strong>Regional holidays</strong> apply to specific countries or cities.</p>
                        <p class="text-muted small"><i class="bi bi-dot"></i> <strong>Recurring holidays</strong> repeat every year on the same date.</p>
                        <p class="text-muted small mb-0"><i class="bi bi-dot"></i> Holidays are excluded from leave day calculations.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            function initHolidayForm() {
                if (typeof $ === 'undefined' || typeof $ === 'undefined') {
                    setTimeout(initHolidayForm, 100);
                    return;
                }

                $(document).ready(function () {
                    var $holidayTypeSelect = $('#holiday_type');
                    var $countryField = $('#country-field');
                    var $cityField = $('#city-field');
                    var $countrySelect = $('#country_id');
                    var $citySelect = $('#city_id');

                    function toggleLocationFields() {
                        if ($holidayTypeSelect.val() === 'regional') {
                            $countryField.show();
                            $cityField.show();
                        } else {
                            $countryField.hide();
                            $cityField.hide();
                            $countrySelect.val(null).trigger('change');
                            $citySelect.val(null).trigger('change');
                        }
                    }

                    // Initialize on page load
                    @if($editing && $record?->holiday_type === 'regional')
                        $countryField.show();
                        $cityField.show();
                    @endif

                    $holidayTypeSelect.on('change', toggleLocationFields);
                });
            }

            initHolidayForm();
        </script>
    @endpush
@endsection
