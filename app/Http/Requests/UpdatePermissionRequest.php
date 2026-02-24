<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePermissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $permissionId = $this->route('permission')?->id;

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, Closure $fail) use ($permissionId) {
                    $exists = \DB::table('permissions')
                        ->where('name', $value)
                        ->where('id', '!=', $permissionId)
                        ->exists();

                    if ($exists) {
                        $fail('A permission with this name already exists.');
                    }
                },
            ],
            'guard_name' => 'sometimes|string|in:web,api',
            'module' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The permission name is required.',
            'guard_name.in' => 'The guard must be either web or api.',
        ];
    }
}
