<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController\BaseController;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends BaseController
{
    public function __construct(UserDataTableController $dataTableController)
    {
        $this->model = User::class;
        $this->routePrefix = 'users';
        $this->viewPrefix = 'users';
        $this->resourceName = 'User';
        $this->dataTableController = $dataTableController;
    }

    protected function authorizeAction(string $ability, ?Model $record = null): void
    {
        if ($record) {
            $this->authorize($ability, $record);
        } else {
            $this->authorize($ability, User::class);
        }
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
        $this->authorize('view', $user);
        $user->load(['roles', 'employee', 'leaveBalances', 'leaveRequests']);

        return view('users.show', compact('user'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        if ($request->filled('role_id')) {
            $user->syncRoles([Role::findById($request->input('role_id'))]);
        }

        return $this->successRedirect('created');
    }

    public function update(Request $request, int|string $record): RedirectResponse
    {
        $user = $this->findRecord($record);
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        // Sync single role
        if ($request->filled('role_id')) {
            $user->syncRoles([Role::findById($request->input('role_id'))]);
        }

        return $this->successRedirect('updated');
    }
}
