<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\PostCategoryRequest;
use App\Models\PostCategory;
use Illuminate\View\View;

class PostCategoryController extends BaseController
{
    public function __construct(PostCategoryDataTableController $dataTableController)
    {
        $this->model = PostCategory::class;
        $this->routePrefix = 'post-categories';
        $this->viewPrefix = 'post-categories';
        $this->resourceName = 'Post Category';
        $this->dataTableController = $dataTableController;
    }

    protected function requestClass(): ?string
    {
        return PostCategoryRequest::class;
    }

    public function show(int|string $record): View
    {
        $postCategory = $this->findRecord($record);
        $this->authorizeAction('view', $postCategory);

        return view('post-categories.show', compact('postCategory'));
    }
}
