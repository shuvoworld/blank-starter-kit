@extends('layouts.app')

@section('header')
<h1 class="m-0">Add Leave Type</h1>
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item"><a href="{{ route('leave-types.index') }}">Leave Types</a></li>
<li class="breadcrumb-item active" aria-current="page">Create</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-calendar-check me-2"></i>
                    New Leave Type
                </h3>
            </div>
            <form action="{{ route('leave-types.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}"
                                   placeholder="e.g., Sick Leave" required autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="code" class="form-label">Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('code') is-invalid @enderror"
                                   id="code" name="code" value="{{ old('code') }}"
                                   placeholder="e.g., SL" required style="text-transform: uppercase;">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description"
                                      rows="3" placeholder="Leave type description">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="is_paid" class="form-label">Is Paid <span class="text-danger">*</span></label>
                            <select class="form-select @error('is_paid') is-invalid @enderror"
                                    id="is_paid" name="is_paid" required>
                                <option value="1" {{ old('is_paid', 1) == 1 ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ old('is_paid') == 0 ? 'selected' : '' }}>No</option>
                            </select>
                            @error('is_paid')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="requires_approval" class="form-label">Requires Approval <span class="text-danger">*</span></label>
                            <select class="form-select @error('requires_approval') is-invalid @enderror"
                                    id="requires_approval" name="requires_approval" required>
                                <option value="1" {{ old('requires_approval', 1) == 1 ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ old('requires_approval') == 0 ? 'selected' : '' }}>No</option>
                            </select>
                            @error('requires_approval')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="requires_document" class="form-label">Requires Document</label>
                            <select class="form-select @error('requires_document') is-invalid @enderror"
                                    id="requires_document" name="requires_document">
                                <option value="0" {{ old('requires_document', 0) == 0 ? 'selected' : '' }}>No</option>
                                <option value="1" {{ old('requires_document') == 1 ? 'selected' : '' }}>Yes</option>
                            </select>
                            @error('requires_document')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="max_days_per_year" class="form-label">Max Days/Year</label>
                            <input type="number" class="form-control @error('max_days_per_year') is-invalid @enderror"
                                   id="max_days_per_year" name="max_days_per_year" value="{{ old('max_days_per_year') }}"
                                   placeholder="e.g., 12" min="0">
                            @error('max_days_per_year')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="max_days_per_month" class="form-label">Max Days/Month</label>
                            <input type="number" class="form-control @error('max_days_per_month') is-invalid @enderror"
                                   id="max_days_per_month" name="max_days_per_month" value="{{ old('max_days_per_month') }}"
                                   placeholder="e.g., 2" min="0">
                            @error('max_days_per_month')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="max_consecutive_days" class="form-label">Max Consecutive Days</label>
                            <input type="number" class="form-control @error('max_consecutive_days') is-invalid @enderror"
                                   id="max_consecutive_days" name="max_consecutive_days" value="{{ old('max_consecutive_days') }}"
                                   placeholder="e.g., 5" min="0">
                            @error('max_consecutive_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="carry_forward" class="form-label">Carry Forward</label>
                            <select class="form-select @error('carry_forward') is-invalid @enderror"
                                    id="carry_forward" name="carry_forward">
                                <option value="0" {{ old('carry_forward', 0) == 0 ? 'selected' : '' }}>No</option>
                                <option value="1" {{ old('carry_forward') == 1 ? 'selected' : '' }}>Yes</option>
                            </select>
                            @error('carry_forward')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="carry_forward_limit" class="form-label">Carry Forward Limit</label>
                            <input type="number" class="form-control @error('carry_forward_limit') is-invalid @enderror"
                                   id="carry_forward_limit" name="carry_forward_limit" value="{{ old('carry_forward_limit') }}"
                                   placeholder="e.g., 5" min="0">
                            @error('carry_forward_limit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="carry_forward_expiry_days" class="form-label">Expiry Days</label>
                            <input type="number" class="form-control @error('carry_forward_expiry_days') is-invalid @enderror"
                                   id="carry_forward_expiry_days" name="carry_forward_expiry_days" value="{{ old('carry_forward_expiry_days') }}"
                                   placeholder="e.g., 90" min="0">
                            @error('carry_forward_expiry_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Days after which carried leave expires</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="min_days_before_request" class="form-label">Min Notice Days</label>
                            <input type="number" class="form-control @error('min_days_before_request') is-invalid @enderror"
                                   id="min_days_before_request" name="min_days_before_request" value="{{ old('min_days_before_request') }}"
                                   placeholder="e.g., 3" min="0">
                            @error('min_days_before_request')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Days advance notice required</div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="is_gender_specific" class="form-label">Gender Specific</label>
                            <select class="form-select @error('is_gender_specific') is-invalid @enderror"
                                    id="is_gender_specific" name="is_gender_specific">
                                <option value="0" {{ old('is_gender_specific', 0) == 0 ? 'selected' : '' }}>No</option>
                                <option value="1" {{ old('is_gender_specific') == 1 ? 'selected' : '' }}>Yes</option>
                            </select>
                            @error('is_gender_specific')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="applicable_gender" class="form-label">Applicable Gender</label>
                            <select class="form-select @error('applicable_gender') is-invalid @enderror"
                                    id="applicable_gender" name="applicable_gender">
                                <option value="">Not Applicable</option>
                                <option value="male" {{ old('applicable_gender') == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('applicable_gender') == 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ old('applicable_gender') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('applicable_gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="is_paid_pro_rata" class="form-label">Pro-rata Calculation</label>
                            <select class="form-select @error('is_paid_pro_rata') is-invalid @enderror"
                                    id="is_paid_pro_rata" name="is_paid_pro_rata">
                                <option value="0" {{ old('is_paid_pro_rata', 0) == 0 ? 'selected' : '' }}>No</option>
                                <option value="1" {{ old('is_paid_pro_rata') == 1 ? 'selected' : '' }}>Yes</option>
                            </select>
                            @error('is_paid_pro_rata')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">For mid-year joiners</div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="sort_order" class="form-label">Sort Order</label>
                            <input type="number" class="form-control @error('sort_order') is-invalid @enderror"
                                   id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}"
                                   placeholder="0" min="0">
                            @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3 mb-3">
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
                        <i class="bi bi-check-lg me-1"></i> Create Leave Type
                    </button>
                    <a href="{{ route('leave-types.index') }}" class="btn btn-secondary">
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
                    <i class="bi bi-dot"></i> Code must be unique and short (e.g., SL, AL).
                </p>
                <p class="text-muted small">
                    <i class="bi bi-dot"></i> Max days per year/month limit leave allocation.
                </p>
                <p class="text-muted small">
                    <i class="bi bi-dot"></i> Carry forward allows unused leave to next year.
                </p>
                <p class="text-muted small mb-0">
                    <i class="bi bi-dot"></i> Only active leave types will be available for selection.
                </p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('code').addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    });
</script>
@endpush
@endsection
