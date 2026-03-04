<?php

namespace App\Policies;

use App\Models\LandingPageSection;
use App\Models\User;

class LandingPageSectionPolicy
{
    /**
     * Super admins skip all checks below and are automatically allowed to do everything.
     *
     * Returning true  = allow immediately, do not run the specific method.
     * Returning null  = continue to the specific method below.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('Superuser')) {
            return true;
        }

        return null;
    }

    /**
     * Can the user see the full list of landing page sections?
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view any landing page sections');
    }

    /**
     * Can the user view a single landing page section record?
     */
    public function view(User $user, LandingPageSection $landingPageSection): bool
    {
        return $user->can('view any landing page sections');
    }

    /**
     * Can the user open the create form and submit a new section?
     */
    public function create(User $user): bool
    {
        return $user->can('create landing page sections');
    }

    /**
     * Can the user open the edit form and save changes to this section?
     */
    public function update(User $user, LandingPageSection $landingPageSection): bool
    {
        return $user->can('update landing page sections');
    }

    /**
     * Can the user delete this section record?
     */
    public function delete(User $user, LandingPageSection $landingPageSection): bool
    {
        return $user->can('delete landing page sections');
    }
}
