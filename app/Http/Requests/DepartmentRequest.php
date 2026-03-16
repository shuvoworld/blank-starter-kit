<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseRequest\BaseFormRequest;
use Illuminate\Validation\Rule;

class DepartmentRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $id = $this->recordId();

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('departments', 'name')->ignore($id)],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The department name is required.',
            'name.unique' => 'This department name already exists.',
        ];
    }
}
