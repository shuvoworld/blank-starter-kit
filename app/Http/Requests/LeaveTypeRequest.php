<?php

namespace App\Http\Requests;


use App\Http\Requests\BaseRequest\BaseFormRequest;

class LeaveTypeRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $id = $this->recordId();

        return [
            'name'                      => ['required', 'string', 'max:255', 'unique:leave_types,name' . ($id ? ",{$id}" : '')],
            'code'                      => ['required', 'string', 'max:50', 'unique:leave_types,code' . ($id ? ",{$id}" : '')],
            'description'               => ['required', 'string'],
            'is_paid'                   => ['nullable', 'boolean'],
            'requires_approval'         => ['nullable', 'boolean'],
            'max_days_per_year'         => ['nullable', 'integer', 'min:0'],
            'max_days_per_month'        => ['nullable', 'integer', 'min:0'],
            'carry_forward'             => ['nullable', 'boolean'],
            'carry_forward_limit'       => ['nullable', 'integer', 'min:0'],
            'carry_forward_expiry_days' => ['nullable', 'integer', 'min:0'],
            'requires_document'         => ['nullable', 'boolean'],
            'supporting_document'       => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:5120'],
            'min_days_before_request'   => ['nullable', 'integer', 'min:0'],
            'max_consecutive_days'      => ['nullable', 'integer', 'min:0'],
            'is_gender_specific'        => ['nullable', 'boolean'],
            'applicable_gender'         => ['nullable', 'in:male,female,other'],
            'is_paid_pro_rata'          => ['nullable', 'boolean'],
            'is_active'                 => ['nullable', 'boolean'],
            'sort_order'                => ['nullable', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'        => 'The leave type name is required.',
            'name.unique'          => 'This leave type name already exists.',
            'code.required'        => 'The leave type code is required.',
            'code.unique'          => 'This leave type code already exists.',
            'applicable_gender.in' => 'The applicable gender must be male, female, or other.',
        ];
    }
}
