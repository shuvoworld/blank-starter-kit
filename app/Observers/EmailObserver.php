<?php

namespace App\Observers;

use App\Models\Email;

class EmailObserver
{
    public function creating(Email $email): void
    {
        $email->created_by = auth()->id();
        $email->updated_by = auth()->id();
    }

    public function updating(Email $email): void
    {
        $email->updated_by = auth()->id();
    }

    public function deleting(Email $email): void
    {
        $email->deleted_by = auth()->id();
        $email->saveQuietly();
    }
}
