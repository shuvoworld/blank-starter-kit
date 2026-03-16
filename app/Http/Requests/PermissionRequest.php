<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseRequest\BaseFormRequest;
use Closure;

class PermissionRequest extends BaseFormRequest
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
                    $exists = \DB::table('permissions')
                        ->where('name', $value)
                        ->when($id, fn ($q) => $q->where('id', '!=', $id))
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

    public function messages(): array
    {
        return [
            'name.required' => 'The permission name is required.',
            'guard_name.in' => 'The guard must be either web or api.',
        ];
    }
}
