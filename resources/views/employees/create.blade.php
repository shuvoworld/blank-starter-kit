@extends('layouts.app')

@section('header')
<h1 class="m-0">Add Employee</h1>
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('employees.index') }}">Employees</a></li>
        <li class="breadcrumb-item active" aria-current="page">Create</li>
@endsection

@section('content')
<div class="row">
        <div class="col-lg-8">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-person-plus me-2"></i>
                        New Employee
                    </h3>
                </div>
                <form action="{{ route('employees.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="profile_picture" class="form-label">Profile Picture</label>
                                <input type="file" class="form-control @error('profile_picture') is-invalid @enderror"
                                       id="profile_picture" name="profile_picture"
                                       accept="image/jpeg,image/png,image/gif,image/webp">
                                @error('profile_picture')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Allowed: JPG, PNG, GIF, WebP (Max: 5MB)</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name') }}"
                                       placeholder="Full name" required autofocus>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       id="email" name="email" value="{{ old('email') }}"
                                       placeholder="email@example.com" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                       id="phone" name="phone" value="{{ old('phone') }}"
                                       placeholder="+1 (555) 000-0000">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="hire_date" class="form-label">Hire Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('hire_date') is-invalid @enderror"
                                       id="hire_date" name="hire_date" value="{{ old('hire_date') }}" required>
                                @error('hire_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="department" class="form-label">Department <span class="text-danger">*</span></label>
                                <select class="form-select @error('department') is-invalid @enderror"
                                        id="department" name="department" required>
                                    <option value="">Select department...</option>
                                    @foreach(['Engineering', 'Marketing', 'Sales', 'HR', 'Finance', 'IT', 'Operations'] as $dept)
                                        <option value="{{ $dept }}" {{ old('department') === $dept ? 'selected' : '' }}>
                                            {{ $dept }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('department')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="position" class="form-label">Position <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('position') is-invalid @enderror"
                                       id="position" name="position" value="{{ old('position') }}"
                                       placeholder="e.g. Senior Developer" required>
                                @error('position')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="salary" class="form-label">Salary <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control @error('salary') is-invalid @enderror"
                                           id="salary" name="salary" value="{{ old('salary') }}"
                                           placeholder="0.00" min="0" step="0.01" required>
                                    @error('salary')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror"
                                        id="status" name="status" required>
                                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Documents Section --}}
                        <hr class="my-4">
                        <h5 class="mb-3"><i class="bi bi-file-earmark-pdf me-2"></i>Documents</h5>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="resume" class="form-label">Resume/CV</label>
                                <input type="file" class="form-control @error('resume') is-invalid @enderror"
                                       id="resume" name="resume" accept=".pdf,application/pdf">
                                @error('resume')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Upload employee resume in PDF format (Max: 5MB)</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="certificates" class="form-label">Certificates</label>
                                <input type="file" class="form-control @error('certificates') is-invalid @enderror"
                                       id="certificates" name="certificates[]" accept=".pdf,application/pdf" multiple>
                                @error('certificates')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Upload employee certificates (PDF only, Max: 5 files, 5MB each)</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="documents" class="form-label">Other Documents</label>
                                <input type="file" class="form-control @error('documents') is-invalid @enderror"
                                       id="documents" name="documents[]" accept=".pdf,application/pdf" multiple>
                                @error('documents')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Upload other employee documents (PDF only, Max: 10 files, 5MB each)</div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Create Employee
                        </button>
                        <a href="{{ route('employees.index') }}" class="btn btn-secondary">
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
                        <i class="bi bi-dot"></i> Email must be unique across all employees.
                    </p>
                    <p class="text-muted small">
                        <i class="bi bi-dot"></i> Phone number is optional.
                    </p>
                    <p class="text-muted small mb-0">
                        <i class="bi bi-dot"></i> Salary should be entered as an annual amount.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
