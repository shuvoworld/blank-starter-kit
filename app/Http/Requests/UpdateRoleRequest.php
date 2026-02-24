<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
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
        $roleId = $this->route('role')?->id;

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, Closure $fail) use ($roleId) {
                    $exists = \DB::table('roles')
                        ->where('name', $value)
                        ->where('id', '!=', $roleId)
                        ->exists();

                    if ($exists) {
                        $fail('A role with this name already exists.');
                    }
                },
            ],
            'guard_name' => 'sometimes|string|in:web,api',
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
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
            'name.required' => 'The role name is required.',
            'guard_name.in' => 'The guard must be either web or api.',
            'permissions.*.exists' => 'One or more selected permissions do not exist.',
        ];
    }
}
