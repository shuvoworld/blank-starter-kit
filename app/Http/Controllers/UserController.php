<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignRolesToUserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(User::query()->with('roles'))
                ->addIndexColumn()
                ->addColumn('created_at_formatted', function ($user) {
                    return $user->created_at->format('M d, Y');
                })
                ->addColumn('roles', function ($user) {
                    return $user->roles->pluck('name')->map(function ($name) {
                        return '<span class="badge bg-primary">'.e($name).'</span>';
                    })->implode(' ');
                })
                ->addColumn('action', function ($user) {
                    $editUrl = route('users.edit', $user);
                    $deleteUrl = route('users.destroy', $user);

                    return '
                        <div class="btn-group btn-group-sm">
                            <a href="'.$editUrl.'" class="btn btn-primary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button type="button" class="btn btn-danger btn-delete"
                                data-url="'.$deleteUrl.'"
                                data-name="'.e($user->name).'" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    ';
                })
                ->rawColumns(['roles', 'action'])
                ->make(true);
        }

        return view('users.index');
    }

    public function create(): View
    {
        // $roles = Role::all();
        return view('users.create', compact('routeName', 'moduleName'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        if ($request->filled('roles')) {
            $roles = Role::whereIn('id', $request->input('roles'))->get();
            $user->syncRoles($roles);
        }

        return to_route('users.index')->with('status', 'User created successfully.');
    }

    public function show(User $user): RedirectResponse
    {
        return to_route('users.edit', $user);
    }

    public function edit(User $user): View
    {
        $user->load('roles');
        $roles = Role::all();
        $userRoles = $user->roles->pluck('id')->toArray();

        return view('users.edit', compact('user', 'roles', 'userRoles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        // Sync roles - convert IDs to Role objects
        $roleIds = $request->input('roles', []);
        if (empty($roleIds)) {
            $user->syncRoles([]);
        } else {
            $roles = Role::whereIn('id', $roleIds)->get();
            $user->syncRoles($roles);
        }

        return to_route('users.index')->with('status', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return to_route('users.index')->with('status', 'User deleted successfully.');
    }

    /**
     * Show the form for managing user roles.
     */
    public function editRoles(User $user): View
    {
        $user->load('roles');
        $roles = Role::all();
        $userRoles = $user->roles->pluck('id')->toArray();

        return view('users.roles', compact('user', 'roles', 'userRoles'));
    }

    /**
     * Update user roles.
     */
    public function updateRoles(AssignRolesToUserRequest $request, User $user): RedirectResponse
    {
        $roleIds = $request->input('roles', []);
        if (empty($roleIds)) {
            $user->syncRoles([]);
        } else {
            $roles = Role::whereIn('id', $roleIds)->get();
            $user->syncRoles($roles);
        }

        return back()->with('status', 'Roles updated successfully.');
    }
}
