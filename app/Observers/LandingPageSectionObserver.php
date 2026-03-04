<?php

namespace App\Observers;

use App\Models\LandingPageSection;

class LandingPageSectionObserver
{
    /**
     * Handle the LandingPageSection "created" event.
     *
     * This fires after a new section row has been saved to the database.
     * Add any side effects that must happen right after creation.
     */
    public function created(LandingPageSection $landingPageSection): void
    {
        // Example: clear cached landing page content.
        // cache()->forget('landing_page_content');
    }

    /**
     * Handle the LandingPageSection "updated" event.
     *
     * This fires after a section row has been changed and saved.
     * Add any side effects that must happen when section details change.
     */
    public function updated(LandingPageSection $landingPageSection): void
    {
        // Clear cached landing page content when sections are updated.
        // cache()->forget('landing_page_content');
    }

    /**
     * Handle the LandingPageSection "deleting" event.
     *
     * This fires BEFORE the section is removed from the database.
     * Add any cascading effects here.
     */
    public function deleting(LandingPageSection $landingPageSection): void
    {
        // Add cascade logic here if deleting this section should affect related records.
    }

    /**
     * Handle the LandingPageSection "deleted" event.
     *
     * This fires AFTER the section has been removed from the database.
     * Add any final cleanup actions here.
     */
    public function deleted(LandingPageSection $landingPageSection): void
    {
        // Clear any caches that held this section's data.
        // cache()->forget('landing_page_content');
    }
}
