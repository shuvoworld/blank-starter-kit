<?php

namespace App\Policies;

class DesignationPolicy extends BasePolicy
{
    protected function resource(): string
    {
        return 'designations';
    }
}
