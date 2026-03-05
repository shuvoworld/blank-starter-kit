<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     *
     * This fires after a new user row has been saved to the database.
     * Add any side effects that must happen right after creation.
     */
    public function created(User $user): void
    {
        // Example: send welcome email, create employee record, etc.
        // event(new UserRegistered($user));
    }

    /**
     * Handle the User "updated" event.
     *
     * This fires after a user row has been changed and saved.
     * Add any side effects that must happen when user details change.
     */
    public function updated(User $user): void
    {
        // Example: log profile changes, sync with external systems
        // if ($user->wasChanged('email')) { ... }
    }

    /**
     * Handle the User "deleting" event.
     *
     * This fires BEFORE the user is removed from the database.
     * This is the correct place to handle cascading effects.
     */
    public function deleting(User $user): void
    {
        // Cascade: Handle related records before deletion
        // - Leave requests will be handled by database foreign key constraints
        // - Leave balances will be handled by database foreign key constraints
        // - Employee record should be handled if it exists
        if ($user->employee) {
            $user->employee->delete();
        }
    }

    /**
     * Handle the User "deleted" event.
     *
     * This fires AFTER the user has been removed from the database.
     * Add any final cleanup actions here.
     */
    public function deleted(User $user): void
    {
        // Add any final cleanup here
    }
}
