<?php

namespace App\Observers;

use App\Models\PostCategory;

class PostCategoryObserver
{
    public function creating(PostCategory $postCategory): void
    {
        $postCategory->created_by = auth()->id();
        $postCategory->updated_by = auth()->id();
    }

    public function updating(PostCategory $postCategory): void
    {
        $postCategory->updated_by = auth()->id();
    }

    public function deleting(PostCategory $postCategory): void
    {
        $postCategory->deleted_by = auth()->id();
        $postCategory->saveQuietly();
    }
}
