<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\StoreEmailRequest;
use App\Http\Requests\UpdateEmailRequest;
use App\Models\Email;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmailController extends BaseController
{
    public function __construct(EmailDataTableController $dataTableController)
    {
        $this->model = Email::class;
        $this->routePrefix = 'emails';
        $this->viewPrefix = 'emails';
        $this->resourceName = 'Email';
        $this->dataTableController = $dataTableController;
    }

    public function show(int|string $record): View
    {
        $email = $this->findRecord($record);
        $this->authorizeAction('view', $email);

        return view('emails.show', compact('email'));
    }

    public function store(StoreEmailRequest $request): RedirectResponse
    {
        $this->authorizeAction('create');

        Email::create($request->validated());

        return $this->successRedirect('created');
    }

    public function update(UpdateEmailRequest $request, int|string $record): RedirectResponse
    {
        $email = $this->findRecord($record);
        $this->authorizeAction('update', $email);

        $email->update($request->validated());

        return $this->successRedirect('updated');
    }
}
