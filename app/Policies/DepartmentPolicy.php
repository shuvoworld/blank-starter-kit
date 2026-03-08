<?php

namespace App\Policies;

class DepartmentPolicy extends BasePolicy
{
    protected function resource(): string
    {
        return 'departments';
    }
}
