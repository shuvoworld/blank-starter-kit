@extends('layouts.app')

@section('header')
<h1 class="m-0">Edit Employee</h1>
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('employees.index') }}">Employees</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('content')
<div class="row">
        <div class="col-lg-8">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-pencil me-2"></i>
                        Edit Employee: {{ $employee->name }}
                    </h3>
                </div>
                <form action="{{ route('employees.update', $employee) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        {{-- Profile Picture Section --}}
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label">Profile Picture</label>
                                <div class="d-flex align-items-center gap-3">
                                    @if($employee->getFirstMediaUrl('profile_picture'))
                                        <img src="{{ $employee->getFirstMediaUrl('profile_picture', 'thumb') }}"
                                             alt="{{ $employee->name }}"
                                             class="rounded-circle"
                                             width="80"
                                             height="80"
                                             style="object-fit: cover;">
                                    @else
                                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center text-white"
                                             style="width: 80px; height: 80px; font-size: 2rem;">
                                            <i class="bi bi-person-fill"></i>
                                        </div>
                                    @endif
                                    <div class="flex-grow-1">
                                        <input type="file"
                                               class="form-control @error('profile_picture') is-invalid @enderror"
                                               id="profile_picture"
                                               name="profile_picture"
                                               accept="image/jpeg,image/png,image/gif,image/webp">
                                        @error('profile_picture')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Leave empty to keep current image</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name', $employee->name) }}"
                                       placeholder="Full name" required autofocus>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       id="email" name="email" value="{{ old('email', $employee->email) }}"
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
                                       id="phone" name="phone" value="{{ old('phone', $employee->phone) }}"
                                       placeholder="+1 (555) 000-0000">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="hire_date" class="form-label">Hire Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('hire_date') is-invalid @enderror"
                                       id="hire_date" name="hire_date"
                                       value="{{ old('hire_date', $employee->hire_date->format('Y-m-d')) }}" required>
                                @error('hire_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="department_id" class="form-label">Department</label>
                                <select class="form-select @error('department_id') is-invalid @enderror"
                                        id="department_id" name="department_id">
                                    <option value="">Select department...</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ old('department_id', $employee->department_id) == $dept->id ? 'selected' : '' }}>
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
                                        id="designation_id" name="designation_id">
                                    <option value="">Select designation...</option>
                                    @foreach($designations as $designation)
                                        <option value="{{ $designation->id }}" {{ old('designation_id', $employee->designation_id) == $designation->id ? 'selected' : '' }}>
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
                            <div class="col-md-6 mb-3">
                                <label for="salary" class="form-label">Salary <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control @error('salary') is-invalid @enderror"
                                           id="salary" name="salary" value="{{ old('salary', $employee->salary) }}"
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
                                    <option value="active" {{ old('status', $employee->status) === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $employee->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Documents Section --}}
                        <hr class="my-4">
                        <h5 class="mb-3"><i class="bi bi-file-earmark-pdf me-2"></i>Documents</h5>

                        {{-- Resume --}}
                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="resume" class="form-label">Resume/CV</label>
                                @if($employee->getFirstMedia('resume'))
                                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                                        <i class="bi bi-file-earmark-pdf-fill me-2"></i>
                                        <strong>Current:</strong> {{ $employee->getFirstMedia('resume')->file_name }}
                                        <a href="{{ $employee->getFirstMediaUrl('resume') }}" target="_blank" class="btn btn-sm btn-outline-primary ms-2">
                                            <i class="bi bi-download"></i> Download
                                        </a>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif
                                <input type="file" class="form-control @error('resume') is-invalid @enderror"
                                       id="resume" name="resume" accept=".pdf,application/pdf">
                                @error('resume')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Upload employee resume in PDF format (Max: 5MB). New file will replace the existing one.</div>
                            </div>
                        </div>

                        {{-- Certificates --}}
                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="certificates" class="form-label">Certificates</label>
                                @if($employee->getMedia('certificates')->count() > 0)
                                    <div class="mb-2">
                                        <small class="text-muted">Current certificates:</small>
                                        @foreach($employee->getMedia('certificates') as $cert)
                                            <div class="d-inline-block me-2">
                                                <a href="{{ $cert->getUrl() }}" target="_blank" class="badge bg-info text-decoration-none">
                                                    <i class="bi bi-file-earmark-pdf-fill me-1"></i>{{ $cert->file_name }}
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                <input type="file" class="form-control @error('certificates') is-invalid @enderror"
                                       id="certificates" name="certificates[]" accept=".pdf,application/pdf" multiple>
                                @error('certificates')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Upload employee certificates (PDF only, Max: 5 files, 5MB each). New files will be added to existing ones.</div>
                            </div>
                        </div>

                        {{-- Other Documents --}}
                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="documents" class="form-label">Other Documents</label>
                                @if($employee->getMedia('documents')->count() > 0)
                                    <div class="mb-2">
                                        <small class="text-muted">Current documents:</small>
                                        @foreach($employee->getMedia('documents') as $doc)
                                            <div class="d-inline-block me-2">
                                                <a href="{{ $doc->getUrl() }}" target="_blank" class="badge bg-info text-decoration-none">
                                                    <i class="bi bi-file-earmark-pdf-fill me-1"></i>{{ $doc->file_name }}
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                <input type="file" class="form-control @error('documents') is-invalid @enderror"
                                       id="documents" name="documents[]" accept=".pdf,application/pdf" multiple>
                                @error('documents')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Upload other employee documents (PDF only, Max: 10 files, 5MB each). New files will be added to existing ones.</div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Update Employee
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
                        Employee Metadata
                    </h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">ID:</td>
                            <td>{{ $employee->id }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Created:</td>
                            <td>{{ $employee->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Updated:</td>
                            <td>{{ $employee->updated_at->format('M d, Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            @can('delete employees')
                <div class="card card-danger card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Danger Zone
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">Once deleted, this employee record cannot be recovered.</p>
                        <form action="{{ route('employees.destroy', $employee) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Are you sure you want to delete {{ $employee->name }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="bi bi-trash me-1"></i> Delete Employee
                            </button>
                        </form>
                    </div>
                </div>
            @endcan
        </div>
    </div>
@endsection
