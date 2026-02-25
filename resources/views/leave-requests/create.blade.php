@extends('layouts.app')

@section('header')
<h1 class="m-0">Submit Leave Request</h1>
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
<li class="breadcrumb-item"><a href="{{ route('my-leave-requests.index') }}">My Leave Requests</a></li>
<li class="breadcrumb-item active" aria-current="page">New Request</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-calendar-plus me-2"></i>
                    New Leave Request
                </h3>
            </div>
            <form action="{{ route('my-leave-requests.store') }}" method="POST" id="leaveRequestForm">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="leave_type_id" class="form-label">Leave Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('leave_type_id') is-invalid @enderror"
                                    id="leave_type_id" name="leave_type_id" required>
                                <option value="">Select Leave Type</option>
                                @foreach($leaveTypes as $type)
                                    <option value="{{ $type->id }}" data-max-days="{{ $type->max_days_per_year }}"
                                            data-carry-forward="{{ $type->carry_forward }}"
                                            data-description="{{ $type->description }}"
                                            {{ old('leave_type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('leave_type_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="duration_display" class="form-label">Duration (Days)</label>
                            <input type="text" class="form-control" id="duration_display" readonly
                                   placeholder="Select dates to calculate" value="">
                            <input type="hidden" name="total_days" id="total_days" value="0">
                            <div class="form-text">Excluding weekends and holidays</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                                   id="start_date" name="start_date" value="{{ old('start_date') }}"
                                   required min="{{ today()->format('Y-m-d') }}">
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('end_date') is-invalid @enderror"
                                   id="end_date" name="end_date" value="{{ old('end_date') }}"
                                   required min="{{ today()->format('Y-m-d') }}">
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="reason" class="form-label">Reason</label>
                            <textarea class="form-control @error('reason') is-invalid @enderror"
                                      id="reason" name="reason"
                                      rows="4" placeholder="Please provide a reason for your leave request...">{{ old('reason') }}</textarea>
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div id="leave-balance-info" class="alert alert-info" style="display: none;">
                        <h6><i class="bi bi-info-circle me-2"></i>Your Leave Balance</h6>
                        <div class="d-flex justify-content-between">
                            <span>Entitlement:</span>
                            <strong id="balance-entitlement">—</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Taken:</span>
                            <strong id="balance-taken">—</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Remaining:</span>
                            <strong id="balance-remaining" class="text-success">—</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Pending:</span>
                            <strong id="balance-pending" class="text-warning">—</strong>
                        </div>
                    </div>

                    <div id="leave-type-description" class="alert alert-secondary" style="display: none;">
                        <i class="bi bi-file-text me-2"></i>
                        <span id="description-text"></span>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="bi bi-check-lg me-1"></i> Submit Request
                    </button>
                    <a href="{{ route('my-leave-requests.index') }}" class="btn btn-secondary">
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
                    <i class="bi bi-dot"></i> Leave duration is calculated excluding weekends (Saturday/Sunday) and holidays.
                </p>
                <p class="text-muted small">
                    <i class="bi bi-dot"></i> Your remaining balance will be checked before submission.
                </p>
                <p class="text-muted small mb-0">
                    <i class="bi bi-dot"></i> Pending requests require admin approval before being deducted from your balance.
                </p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let balanceCache = {};

    document.addEventListener('DOMContentLoaded', function() {
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const leaveTypeSelect = document.getElementById('leave_type_id');
        const durationDisplay = document.getElementById('duration_display');
        const totalDaysInput = document.getElementById('total_days');
        const balanceInfo = document.getElementById('leave-balance-info');
        const typeDescription = document.getElementById('leave-type-description');

        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        startDateInput.min = today;
        endDateInput.min = today;

        // Calculate duration when dates change
        function calculateDuration() {
            const startDate = startDateInput.value;
            const endDate = endDateInput.value;

            if (startDate && endDate) {
                const start = new Date(startDate);
                const end = new Date(endDate);

                if (end >= start) {
                    // Simple calculation (actual calculation happens server-side)
                    const totalDays = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
                    durationDisplay.value = totalDays + ' day' + (totalDays > 1 ? 's' : '');
                    totalDaysInput.value = totalDays;

                    // Check balance if leave type is selected
                    if (leaveTypeSelect.value) {
                        checkBalance();
                    }
                } else {
                    durationDisplay.value = '';
                    totalDaysInput.value = 0;
                }
            } else {
                durationDisplay.value = '';
                totalDaysInput.value = 0;
            }
        }

        // Fetch balance for selected leave type
        function checkBalance() {
            const leaveTypeId = leaveTypeSelect.value;
            const year = new Date().getFullYear();

            if (!leaveTypeId || !startDateInput.value) {
                balanceInfo.style.display = 'none';
                return;
            }

            // Show loading state
            balanceInfo.style.display = 'block';
            document.getElementById('balance-entitlement').textContent = 'Loading...';

            // Check cache first
            const cacheKey = leaveTypeId + '-' + year;
            if (balanceCache[cacheKey]) {
                updateBalanceDisplay(balanceCache[cacheKey]);
                return;
            }

            // Fetch from server
            fetch('{{ route('leave-balances.get-balance') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    user_id: {{ auth()->id() }},
                    leave_type_id: leaveTypeId,
                    year: year
                })
            })
            .then(response => response.json())
            .then(data => {
                balanceCache[cacheKey] = data;
                updateBalanceDisplay(data);
            })
            .catch(error => {
                console.error('Error fetching balance:', error);
                document.getElementById('balance-entitlement').textContent = 'Error';
            });
        }

        function updateBalanceDisplay(data) {
            document.getElementById('balance-entitlement').textContent = data.total_entitlement + ' days';
            document.getElementById('balance-taken').textContent = data.taken_days + ' days';
            document.getElementById('balance-remaining').textContent = data.remaining_days + ' days';
            document.getElementById('balance-pending').textContent = data.pending_days + ' days';
        }

        // Show leave type description
        leaveTypeSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const description = selectedOption.dataset.description;

            if (description) {
                document.getElementById('description-text').textContent = description;
                typeDescription.style.display = 'block';
            } else {
                typeDescription.style.display = 'none';
            }

            checkBalance();
        });

        startDateInput.addEventListener('change', function() {
            // Update end date minimum to start date
            endDateInput.min = this.value;
            if (endDateInput.value && endDateInput.value < this.value) {
                endDateInput.value = this.value;
            }
            calculateDuration();
        });

        endDateInput.addEventListener('change', calculateDuration);

        // Form submission
        document.getElementById('leaveRequestForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Submitting...';
        });
    });
</script>
@endpush
@endsection
