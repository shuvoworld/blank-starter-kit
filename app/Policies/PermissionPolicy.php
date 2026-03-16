<?php

namespace App\Policies;

class PermissionPolicy extends BasePolicy
{
    protected function resource(): string
    {
        return 'permissions';
    }
}
