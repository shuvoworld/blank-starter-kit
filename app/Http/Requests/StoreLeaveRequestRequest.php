<?php

namespace App\Http\Requests;

use App\Models\LeaveType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class StoreLeaveRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'leave_type_id.required' => 'Please select a leave type.',
            'leave_type_id.exists' => 'The selected leave type is invalid.',
            'start_date.required' => 'The start date is required.',
            'start_date.after_or_equal' => 'The start date must be today or in the future.',
            'end_date.required' => 'The end date is required.',
            'end_date.after_or_equal' => 'The end date must be the same or after the start date.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $leaveType = LeaveType::find($this->leave_type_id);

            if (! $leaveType) {
                return;
            }

            $startDate = Carbon::parse($this->start_date);
            $endDate = Carbon::parse($this->end_date);
            $totalDays = $endDate->diffInDays($startDate) + 1;

            // Check max consecutive days
            if ($leaveType->max_consecutive_days && $totalDays > $leaveType->max_consecutive_days) {
                $validator->errors()->add('end_date', "Maximum consecutive days for this leave type is {$leaveType->max_consecutive_days}.");
            }

            // Check minimum days before request
            if ($leaveType->min_days_before_request) {
                $minDate = Carbon::now()->addDays($leaveType->min_days_before_request);
                if ($startDate->lt($minDate)) {
                    $validator->errors()->add('start_date', "This leave type requires {$leaveType->min_days_before_request} days advance notice.");
                }
            }

            // Check gender specific leave
            if ($leaveType->is_gender_specific) {
                $user = auth()->user();
                $employee = $user->employee;

                if ($employee && $employee->gender !== $leaveType->applicable_gender) {
                    $validator->errors()->add('leave_type_id', 'This leave type is not applicable to your gender.');
                }
            }
        });
    }
}
