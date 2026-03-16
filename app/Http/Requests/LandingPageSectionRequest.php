<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseRequest\BaseFormRequest;
use Illuminate\Validation\Rule;

class LandingPageSectionRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $id = $this->recordId();

        return [
            'key' => ['required', 'string', 'max:255', Rule::unique('landing_page_sections', 'key')->ignore($id)],
            'section' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:text,textarea,image,boolean,json,number'],
            'label' => ['required', 'string', 'max:255'],
            'value' => ['nullable', 'string', 'max:65535'],
            'options' => ['nullable', 'array'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'key.required' => 'The key field is required.',
            'key.unique' => 'This key already exists.',
            'section.required' => 'The section field is required.',
            'type.required' => 'The type field is required.',
            'type.in' => 'The selected type is invalid.',
            'label.required' => 'The label field is required.',
        ];
    }
}
