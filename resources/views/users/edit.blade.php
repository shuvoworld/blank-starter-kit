@extends('layouts.form.app')
@section('header')
    <h1 class="m-0">Edit User</h1>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-pencil me-2"></i>
                        Edit User: {{ $user->name }}
                    </h3>
                </div>
                <form action="{{ route('users.update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="card-body">
                            @include('layouts.form.inputs.text', ['var' => ['name'=> 'name','label'=> 'Name','placeholder' => 'Name','div'=> 'col-md-12 col-sm-12','value'=> $user->name,'required' => true]])
                            @include('layouts.form.inputs.text', ['var' => ['name'=> 'email','label'=> 'Email','placeholder' => 'email','div'=> 'col-md-12 col-sm-12','value'=> $user->email,'required' => true]])
                            @include('layouts.form.inputs.select-model', ['var' => [
    'name'=> 'roles',
'label'=> 'Roles',
'model' => \App\Models\Role::class,
'div'=> 'col-md-12 col-sm-12',
'class'=>'select2',
'required' => true]
])

                            <div class="mb-3">
                                <label for="roles" class="form-label">Roles</label>
                                <select class="form-select select2 @error('roles') is-invalid @enderror"
                                        id="roles" name="roles[]" multiple>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ in_array($role->id, $userRoles) ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('roles')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Select one or more roles for this user</div>
                            </div>

                            <hr class="my-4">
                            <p class="text-muted small">Leave password fields empty to keep the current password.</p>
                            <div class="mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                       id="password" name="password"
                                       placeholder="Enter new password (optional)">
                                @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control"
                                       id="password_confirmation" name="password_confirmation"
                                       placeholder="Confirm new password">
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i> Update User
                            </button>
                            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-lg me-1"></i> Cancel
                            </a>
                        </div>
                </form>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-info-circle me-2"></i>
                        User Information
                    </h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">ID:</td>
                            <td>{{ $user->id }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Created:</td>
                            <td>{{ $user->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Updated:</td>
                            <td>{{ $user->updated_at->format('M d, Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            @if($user->id !== auth()->id())
                <div class="card card-danger card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Danger Zone
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">Once you delete a user, there is no going back.</p>
                        <button type="button" class="btn btn-danger btn-sm" id="deleteUserBtn">
                            <i class="bi bi-trash me-1"></i> Delete User
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if($user->id !== auth()->id())
        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="deleteModalLabel">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Confirm Delete
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete <strong>{{ $user->name }}</strong>?</p>
                        <p class="text-muted small mb-0">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-trash me-1"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
@push('scripts')
    <script>
        $(function () {
            $('.select2').select2({
                theme: 'bootstrap-5',
                placeholder: 'Select roles',
                allowClear: true,
                width: '100%'
            });

            $('#deleteUserBtn').on('click', function () {
                var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
                deleteModal.show();
            });
        });
    </script>
@endpush
