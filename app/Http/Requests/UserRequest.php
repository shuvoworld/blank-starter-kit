<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseRequest\BaseFormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $id = $this->recordId();

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'password' => $id
                ? ['nullable', 'confirmed', Password::defaults()]
                : ['required', 'confirmed', Password::defaults()],
            'role_id' => ['required', 'exists:roles,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The user name is required.',
            'email.required' => 'An email address is required.',
            'email.unique' => 'This email address is already in use.',
            'password.required' => 'A password is required.',
            'role_id.required' => 'Please select a role.',
            'role_id.exists' => 'The selected role is invalid.',
        ];
    }
}
