<?php

namespace App\Policies;

class RolePolicy extends BasePolicy
{
    protected function resource(): string
    {
        return 'roles';
    }
}
