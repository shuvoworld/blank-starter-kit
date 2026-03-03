@extends('layouts.form.app')

@section('header')
<h1 class="m-0">Add Holiday</h1>
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item"><a href="{{ route('holidays.index') }}">Holidays</a></li>
<li class="breadcrumb-item active" aria-current="page">Create</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-calendar-event me-2"></i>
                    New Holiday
                </h3>
            </div>
            <form action="{{ route('holidays.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Holiday Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}"
                                   placeholder="e.g., New Year's Day" required autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('date') is-invalid @enderror"
                                   id="date" name="date" value="{{ old('date') }}"
                                   required>
                            @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="holiday_type" class="form-label">Holiday Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('holiday_type') is-invalid @enderror"
                                    id="holiday_type" name="holiday_type" required>
                                <option value="global" {{ old('holiday_type', 'global') == 'global' ? 'selected' : '' }}>Global</option>
                                <option value="regional" {{ old('holiday_type') == 'regional' ? 'selected' : '' }}>Regional</option>
                            </select>
                            @error('holiday_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3" id="country-field" style="display: none;">
                            <label for="country_id" class="form-label">Country</label>
                            <select class="form-select @error('country_id') is-invalid @enderror"
                                    id="country_id" name="country_id">
                                <option value="">Select Country</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}" {{ old('country_id') == $country->id ? 'selected' : '' }}>
                                        {{ $country->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('country_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3" id="city-field" style="display: none;">
                            <label for="city_id" class="form-label">City</label>
                            <select class="form-select @error('city_id') is-invalid @enderror"
                                    id="city_id" name="city_id">
                                <option value="">Select City</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city->id }}" {{ old('city_id') == $city->id ? 'selected' : '' }}>
                                        {{ $city->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('city_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                      id="notes" name="notes"
                                      rows="3" placeholder="Additional notes about this holiday">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="is_recurring" class="form-label">Recurring Yearly</label>
                            <select class="form-select @error('is_recurring') is-invalid @enderror"
                                    id="is_recurring" name="is_recurring">
                                <option value="0" {{ old('is_recurring', 0) == 0 ? 'selected' : '' }}>No</option>
                                <option value="1" {{ old('is_recurring') == 1 ? 'selected' : '' }}>Yes</option>
                            </select>
                            @error('is_recurring')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="sort_order" class="form-label">Sort Order</label>
                            <input type="number" class="form-control @error('sort_order') is-invalid @enderror"
                                   id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}"
                                   placeholder="0" min="0">
                            @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="is_active" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select @error('is_active') is-invalid @enderror"
                                    id="is_active" name="is_active" required>
                                <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('is_active') == 0 ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('is_active')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Create Holiday
                    </button>
                    <a href="{{ route('holidays.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-lg me-1"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-info-circle me-2"></i>
                    Information
                </h3>
            </div>
            <div class="card-body">
                <p class="text-muted small">
                    <i class="bi bi-dot"></i> Fields marked with <span class="text-danger">*</span> are required.
                </p>
                <p class="text-muted small">
                    <i class="bi bi-dot"></i> <strong>Global holidays</strong> apply to all employees.
                </p>
                <p class="text-muted small">
                    <i class="bi bi-dot"></i> <strong>Regional holidays</strong> apply to specific countries or cities.
                </p>
                <p class="text-muted small">
                    <i class="bi bi-dot"></i> <strong>Recurring holidays</strong> repeat every year on the same date.
                </p>
                <p class="text-muted small mb-0">
                    <i class="bi bi-dot"></i> Holidays are excluded from leave day calculations.
                </p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var holidayTypeSelect = document.getElementById('holiday_type');
        var countryField = document.getElementById('country-field');
        var cityField = document.getElementById('city-field');

        function toggleLocationFields() {
            if (holidayTypeSelect.value === 'regional') {
                countryField.style.display = 'block';
                cityField.style.display = 'block';
            } else {
                countryField.style.display = 'none';
                cityField.style.display = 'none';
                document.getElementById('country_id').value = '';
                document.getElementById('city_id').value = '';
            }
        }

        holidayTypeSelect.addEventListener('change', toggleLocationFields);
        toggleLocationFields();
    });
</script>
@endpush
@endsection
