<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:employees,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'department' => ['required', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:255'],
            'salary' => ['required', 'numeric', 'min:0'],
            'hire_date' => ['required', 'date'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'profile_picture' => ['nullable', 'image', 'mimes:jpeg,png,gif,webp', 'max:5120'],
            'resume' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'certificates' => ['nullable', 'array', 'max:5'],
            'certificates.*' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'documents' => ['nullable', 'array', 'max:10'],
            'documents.*' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The employee name is required.',
            'email.required' => 'An email address is required.',
            'email.unique' => 'This email address is already in use.',
            'department.required' => 'Please select a department.',
            'position.required' => 'The position is required.',
            'salary.numeric' => 'Salary must be a valid number.',
            'hire_date.required' => 'The hire date is required.',
            'status.in' => 'Status must be active or inactive.',
        ];
    }
}
