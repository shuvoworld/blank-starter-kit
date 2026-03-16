<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseRequest\BaseFormRequest;

class HolidayRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'holiday_type' => ['nullable', 'in:global,regional'],
            'country_id' => ['nullable', 'exists:countries,id'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'is_recurring' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The holiday name is required.',
            'date.required' => 'The holiday date is required.',
            'holiday_type.in' => 'The holiday type must be global or regional.',
            'country_id.exists' => 'The selected country is invalid.',
            'city_id.exists' => 'The selected city is invalid.',
        ];
    }

    public function attributes(): array
    {
        return [
            'country_id' => 'country',
            'city_id' => 'city',
        ];
    }
}
