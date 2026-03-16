<?php

namespace App\Policies;

class PostCategoryPolicy extends BasePolicy
{
    protected function resource(): string
    {
        return 'post-categories';
    }
}
