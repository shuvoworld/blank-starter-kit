<?php

namespace App\Policies;

class HolidayPolicy extends BasePolicy
{
    protected function resource(): string
    {
        return 'holidays';
    }
}
