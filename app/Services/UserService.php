<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use App\Services\BaseService\BaseService;
use Illuminate\Database\Eloquent\Model;

class UserService extends BaseService
{
    protected string $modelClass = User::class;

    /**
     * Strip empty password before update so an unchanged password field
     * does not overwrite the existing hash with a hashed empty string.
     */
    public function update(Model $record, array $data): Model
    {
        if (empty($data['password'])) {
            unset($data['password']);
        }

        return parent::update($record, $data);
    }

    protected function afterCreate(Model $record, array $data): void
    {
        if (! empty($data['role_id'])) {
            $record->syncRoles([Role::findById($data['role_id'])]);
        }
    }

    protected function afterUpdate(Model $record, array $data): void
    {
        if (array_key_exists('role_id', $data) && ! empty($data['role_id'])) {
            $record->syncRoles([Role::findById($data['role_id'])]);
        }
    }
}
