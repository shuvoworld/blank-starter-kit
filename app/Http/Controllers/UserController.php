<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController\BaseController;
use App\Http\Requests\UserRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\View;

class UserController extends BaseController
{
    public function __construct(UserDataTableController $dataTableController, UserService $service)
    {
        $this->model = User::class;
        $this->routePrefix = 'users';
        $this->viewPrefix = 'users';
        $this->resourceName = 'User';
        $this->dataTableController = $dataTableController;
        $this->service = $service;
    }

    protected function requestClass(): ?string
    {
        return UserRequest::class;
    }

    protected function createViewData(): array
    {
        return [
            'roles' => Role::orderBy('name')->get(),
        ];
    }

    protected function editViewData(Model $record): array
    {
        $record->load('roles');

        return [
            'roles' => Role::orderBy('name')->get(),
        ];
    }

    public function show(int|string $record): View
    {
        $user = $this->findRecord($record);
        $this->authorizeAction('view', $user);
        $user->load(['roles', 'employee', 'leaveBalances', 'leaveRequests']);

        return view('users.show', compact('user'));
    }
}
