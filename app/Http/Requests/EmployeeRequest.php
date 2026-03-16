<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseRequest\BaseFormRequest;
use Illuminate\Validation\Rule;

class EmployeeRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $id = $this->recordId();
        $sometimes = $id ? ['sometimes'] : [];

        return [
            'user_id' => ['nullable', 'exists:users,id'],
            'name' => [...$sometimes, 'required', 'string', 'max:255'],
            'email' => [...$sometimes, 'required', 'string', 'email', 'max:255', Rule::unique('employees', 'email')->ignore($id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'designation_id' => ['nullable', 'exists:designations,id'],
            'salary' => [...$sometimes, 'required', 'numeric', 'min:0'],
            'hire_date' => [...$sometimes, 'required', 'date'],
            'status' => [...$sometimes, 'required', 'string', 'in:active,inactive'],
            'country_id' => ['nullable', 'exists:countries,id'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'area_id' => ['nullable', 'exists:areas,id'],
            'profile_picture' => ['nullable', 'image', 'mimes:jpeg,png,gif,webp', 'max:5120'],
            'resume' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'certificates' => ['nullable', 'array', 'max:5'],
            'certificates.*' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'documents' => ['nullable', 'array', 'max:10'],
            'documents.*' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The employee name is required.',
            'email.required' => 'An email address is required.',
            'email.unique' => 'This email address is already in use.',
            'department_id.exists' => 'The selected department is invalid.',
            'designation_id.exists' => 'The selected designation is invalid.',
            'country_id.exists' => 'The selected country is invalid.',
            'city_id.exists' => 'The selected state/city is invalid.',
            'area_id.exists' => 'The selected area is invalid.',
            'salary.numeric' => 'Salary must be a valid number.',
            'hire_date.required' => 'The hire date is required.',
            'status.in' => 'Status must be active or inactive.',
        ];
    }
}
