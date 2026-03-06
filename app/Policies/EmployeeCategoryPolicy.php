<?php

namespace App\Policies;

class EmployeeCategoryPolicy extends BasePolicy
{
    protected function resource(): string
    {
        return 'employee-categories';
    }
}
