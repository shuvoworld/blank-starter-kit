<?php

namespace App\Policies;

class EmailPolicy extends BasePolicy
{
    protected function resource(): string
    {
        return 'emails';
    }
}
