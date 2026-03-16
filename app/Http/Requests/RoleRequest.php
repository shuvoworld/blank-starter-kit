<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseRequest\BaseFormRequest;
use Closure;

class RoleRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $id = $this->recordId();

        return [
            'name' => [
                ...($id ? ['sometimes'] : []),
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, Closure $fail) use ($id): void {
                    $exists = \DB::table('roles')
                        ->where('name', $value)
                        ->when($id, fn ($q) => $q->where('id', '!=', $id))
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

    public function messages(): array
    {
        return [
            'name.required' => 'The role name is required.',
            'guard_name.in' => 'The guard must be either web or api.',
            'permissions.*.exists' => 'One or more selected permissions do not exist.',
        ];
    }
}
