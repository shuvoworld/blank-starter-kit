<?php

namespace App\Policies;

class LandingPageSectionPolicy extends BasePolicy
{
    protected function resource(): string
    {
        return 'landing-page-sections';
    }
}
