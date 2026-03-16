<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseRequest\BaseFormRequest;
use Illuminate\Validation\Rule;

class EmployeeCategoryRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $id = $this->recordId();

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('employee_categories', 'name')->ignore($id)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'name.unique' => 'An Employee Category with this name already exists.',
        ];
    }
}
