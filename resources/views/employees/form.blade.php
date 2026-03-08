@extends('layouts.form.app')

@section('header')
    <h1 class="m-0">{{ $editing ? 'Edit Employee' : 'Add Employee' }}</h1>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('employees.index') }}">Employees</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $editing ? 'Edit' : 'Create' }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-{{ $editing ? 'pencil' : 'person-plus' }} me-2"></i>
                        {{ $editing ? 'Edit Employee: ' . $record->name : 'New Employee' }}
                    </h3>
                </div>
                <form action="{{ $editing ? route('employees.update', $record) : route('employees.store') }}"
                      method="POST" enctype="multipart/form-data">
                    @csrf
                    @if($editing)
                        @method('PUT')
                    @endif

                    <div class="card-body">
                        <div class="row">
                            @include('layouts.form.inputs.media-file', ['var' => [
                                'name'        => 'profile_picture',
                                'label'       => 'Profile Picture',
                                'type'        => 'image-circle',
                                'media'       => $record?->getFirstMedia('profile_picture'),
                                'preview_url' => $record?->getFirstMediaUrl('profile_picture', 'thumb'),
                                'accept'      => 'image/jpeg,image/png,image/gif,image/webp',
                                'div'         => 'col-12',
                                'help_text'   => $editing ? 'Leave empty to keep current image.' : null,
                            ]])
                        </div>

                        <div class="row">
                            @include('layouts.form.inputs.text', ['var' => [
                                'name'        => 'name',
                                'label'       => 'Name',
                                'value'       => $record?->name,
                                'placeholder' => 'Full name',
                                'div'         => 'col-md-6',
                                'required'    => true,
                                'autofocus'   => true,
                            ]])

                            @include('layouts.form.inputs.text', ['var' => [
                                'name'        => 'email',
                                'label'       => 'Email',
                                'type'        => 'email',
                                'value'       => $record?->email,
                                'placeholder' => 'email@example.com',
                                'div'         => 'col-md-6',
                                'required'    => true,
                            ]])
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="user_id" class="form-label">Associate User</label>
                                @if(Auth::user()->hasRole(['Superuser', 'Admin']))
                                    <select class="form-select @error('user_id') is-invalid @enderror"
                                            id="user_id" name="user_id"
                                            data-toggle="select2"
                                            data-placeholder="Select user...">
                                        <option value=""></option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}"
                                                    {{ old('user_id', $record?->user_id) == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }} ({{ $user->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('user_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Link this employee to a system user account.</div>
                                @else
                                    @if($editing && $record->user)
                                        <input type="text" class="form-control"
                                               value="{{ $record->user->name }} ({{ $record->user->email }})" readonly>
                                    @else
                                        <input type="text" class="form-control" value="No user associated" readonly>
                                    @endif
                                    <div class="form-text">Only administrators can change user association.</div>
                                @endif
                            </div>

                            @include('layouts.form.inputs.text', ['var' => [
                                'name'        => 'phone',
                                'label'       => 'Phone',
                                'value'       => $record?->phone,
                                'placeholder' => '+1 (555) 000-0000',
                                'div'         => 'col-md-6',
                            ]])
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="department_id" class="form-label">Department</label>
                                <select class="form-select @error('department_id') is-invalid @enderror"
                                        id="department_id" name="department_id"
                                        data-toggle="select2"
                                        data-placeholder="Select department..."
                                        data-allow-clear="true">
                                    <option value=""></option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}"
                                                {{ old('department_id', $record?->department_id) == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('department_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <a href="{{ route('departments.index') }}" target="_blank" class="text-info">
                                        <i class="bi bi-plus-circle"></i> Add New Department
                                    </a>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="designation_id" class="form-label">Designation</label>
                                <select class="form-select @error('designation_id') is-invalid @enderror"
                                        id="designation_id" name="designation_id"
                                        data-toggle="select2"
                                        data-placeholder="Select designation..."
                                        data-allow-clear="true">
                                    <option value=""></option>
                                    @foreach($designations as $designation)
                                        <option value="{{ $designation->id }}"
                                                {{ old('designation_id', $record?->designation_id) == $designation->id ? 'selected' : '' }}>
                                            {{ $designation->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('designation_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <a href="{{ route('designations.index') }}" target="_blank" class="text-info">
                                        <i class="bi bi-plus-circle"></i> Add New Designation
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            @include('layouts.form.inputs.text', ['var' => [
                                'name'        => 'hire_date',
                                'label'       => 'Hire Date',
                                'type'        => 'date',
                                'value'       => $editing ? $record->hire_date->format('Y-m-d') : $record?->hire_date,
                                'div'         => 'col-md-6',
                                'required'    => true,
                            ]])

                            <div class="col-md-6 mb-3">
                                <label for="salary" class="form-label">Salary <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number"
                                           class="form-control @error('salary') is-invalid @enderror"
                                           id="salary" name="salary"
                                           value="{{ old('salary', $record?->salary) }}"
                                           placeholder="0.00" min="0" step="0.01" required>
                                    @error('salary')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="country_id" class="form-label">Country</label>
                                <select class="form-select @error('country_id') is-invalid @enderror"
                                        id="country_id" name="country_id"
                                        data-toggle="select2"
                                        data-placeholder="Select country..."
                                        data-allow-clear="true">
                                    <option value=""></option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->id }}"
                                                {{ old('country_id', $record?->country_id) == $country->id ? 'selected' : '' }}>
                                            {{ $country->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('country_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="city_id" class="form-label">State/Province</label>
                                <select class="form-select @error('city_id') is-invalid @enderror"
                                        id="city_id" name="city_id"
                                        data-toggle="select2"
                                        data-placeholder="Select state..."
                                        data-allow-clear="true"
                                        {{ !$record?->country_id ? 'disabled' : '' }}>
                                    <option value=""></option>
                                    @foreach($cities as $city)
                                        <option value="{{ $city->id }}"
                                                {{ old('city_id', $record?->city_id) == $city->id ? 'selected' : '' }}>
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
                            <div class="col-md-6 mb-3">
                                <label for="area_id" class="form-label">Area/City</label>
                                <select class="form-select @error('area_id') is-invalid @enderror"
                                        id="area_id" name="area_id"
                                        data-toggle="select2"
                                        data-placeholder="Select area..."
                                        data-allow-clear="true"
                                        {{ !$record?->city_id ? 'disabled' : '' }}>
                                    <option value=""></option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id }}"
                                                {{ old('area_id', $record?->area_id) == $area->id ? 'selected' : '' }}>
                                            {{ $area->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('area_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            @include('layouts.form.inputs.select', ['var' => [
                                'name'     => 'status',
                                'label'    => 'Status',
                                'value'    => $record?->status ?? 'active',
                                'options'  => ['active' => 'Active', 'inactive' => 'Inactive'],
                                'div'      => 'col-md-6',
                                'required' => true,
                                'select2'  => true,
                            ]])
                        </div>

                        {{-- Documents Section --}}
                        <hr class="my-4">
                        <h5 class="mb-3"><i class="bi bi-file-earmark-pdf me-2"></i>Documents</h5>

                        <div class="row">
                            @include('layouts.form.inputs.media-file', ['var' => [
                                'name'      => 'resume',
                                'label'     => 'Resume/CV',
                                'type'      => 'file',
                                'media'     => $record?->getFirstMedia('resume'),
                                'accept'    => '.pdf,application/pdf',
                                'div'       => 'col-12',
                                'help_text' => 'PDF only, max 5MB.' . ($editing ? ' New file replaces the existing one.' : ''),
                            ]])

                            @include('layouts.form.inputs.media-file', ['var' => [
                                'name'      => 'certificates',
                                'label'     => 'Certificates',
                                'type'      => 'file',
                                'media'     => $record?->getMedia('certificates'),
                                'multiple'  => true,
                                'accept'    => '.pdf,application/pdf',
                                'div'       => 'col-12',
                                'help_text' => 'PDF only, max 5 files, 5MB each.' . ($editing ? ' New files are added to existing ones.' : ''),
                            ]])

                            @include('layouts.form.inputs.media-file', ['var' => [
                                'name'      => 'documents',
                                'label'     => 'Other Documents',
                                'type'      => 'file',
                                'media'     => $record?->getMedia('documents'),
                                'multiple'  => true,
                                'accept'    => '.pdf,application/pdf',
                                'div'       => 'col-12',
                                'help_text' => 'PDF only, max 10 files, 5MB each.' . ($editing ? ' New files are added to existing ones.' : ''),
                            ]])
                        </div>
                    </div>

                    @include('layouts.form.includes.footer', ['element' => $record,'module' => 'employees','moduleTitle'=>'Employee'])
                </form>
            </div>
        </div>

        <div class="col-lg-4">
            @if($editing)
                @include('layouts.form.includes.audits', ['element' => $record])
                @include('layouts.form.includes.delete-warning', ['element' => $record,'module' => 'employees','moduleTitle'=>'Employee'])
            @else
                @include('layouts.form.includes.information', ['element' => $record,'module' => 'employees','moduleTitle'=>'Employee'])
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            var citiesByCountryUrl = "{{ route('employees.cities.by-country') }}";
            var areasByCityUrl = "{{ route('employees.areas.by-city') }}";

            function initLocationCascade() {
                if (typeof $ === 'undefined') {
                    setTimeout(initLocationCascade, 100);
                    return;
                }

                $(document).ready(function () {
                    $('#country_id').on('change', function () {
                        var countryId = $(this).val();

                        $('#city_id').empty().append('<option value=""></option>').prop('disabled', true).trigger('change');
                        $('#area_id').empty().append('<option value=""></option>').prop('disabled', true).trigger('change');

                        if (countryId) {
                            $.getJSON(citiesByCountryUrl, {country_id: countryId}, function (data) {
                                $.each(data, function (i, city) {
                                    $('#city_id').append(new Option(city.name, city.id));
                                });
                                $('#city_id').prop('disabled', false).trigger('change');
                            });
                        }
                    });

                    $('#city_id').on('change', function () {
                        var cityId = $(this).val();

                        $('#area_id').empty().append('<option value=""></option>').prop('disabled', true).trigger('change');

                        if (cityId) {
                            $.getJSON(areasByCityUrl, {city_id: cityId}, function (data) {
                                $.each(data, function (i, area) {
                                    $('#area_id').append(new Option(area.name, area.id));
                                });
                                $('#area_id').prop('disabled', false).trigger('change');
                            });
                        }
                    });
                });
            }

            initLocationCascade();
        </script>
    @endpush
@endsection
