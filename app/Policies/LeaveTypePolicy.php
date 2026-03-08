<?php

namespace App\Policies;

class LeaveTypePolicy extends BasePolicy
{
    protected function resource(): string
    {
        return 'leave-types';
    }
}
