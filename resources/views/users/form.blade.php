@extends('layouts.form.app')

@section('header')
    <h1 class="m-0">{{ $editing ? 'Edit User' : 'Create User' }}</h1>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $editing ? 'Edit' : 'Create' }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-{{ $editing ? 'pencil' : 'person-plus' }} me-2"></i>
                        {{ $editing ? 'Edit User: ' . $record->name : 'New User' }}
                    </h3>
                </div>
                <form action="{{ $editing ? route('users.update', $record) : route('users.store') }}"
                      method="POST">
                    @csrf
                    @if($editing) @method('PUT') @endif

                    <div class="card-body">
                        @include('layouts.form.inputs.text', ['var' => [
                            'name'        => 'name',
                            'label'       => 'Name',
                            'value'       => $record?->name,
                            'placeholder' => 'Name',
                            'div'         => 'col-md-12 col-sm-12',
                            'required'    => true,
                            'autofocus'   => true,
                        ]])

                        @include('layouts.form.inputs.text', ['var' => [
                            'name'        => 'email',
                            'label'       => 'Email',
                            'value'       => $record?->email,
                            'placeholder' => 'email',
                            'div'         => 'col-md-12 col-sm-12',
                            'required'    => true,
                        ]])

                        @include('layouts.form.inputs.select', ['var' => [
                            'name'     => 'role_id',
                            'label'    => 'Role',
                            'options'  => $roles->pluck('name', 'id')->toArray(),
                            'prompt'   => 'Select a role',
                            'value'    => $record?->roles->first()?->id ?? null,
                            'div'      => 'col-md-12 col-sm-12',
                            'required' => true,
                            'select2'  => true,
                        ]])

                        @if($editing)
                            <hr class="my-4">
                            <p class="text-muted small">Leave password fields empty to keep the current password.</p>
                        @endif

                        <div class="mb-3">
                            <label for="password" class="form-label">Password @if(!$editing) <span class="text-danger">*</span>@endif</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                   id="password" name="password"
                                   placeholder="{{ $editing ? 'Enter new password (optional)' : 'Enter password' }}"
                                   @if(!$editing) required @endif>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm Password @if(!$editing) <span class="text-danger">*</span>@endif</label>
                            <input type="password" class="form-control"
                                   id="password_confirmation" name="password_confirmation"
                                   placeholder="Confirm password"
                                   @if(!$editing) required @endif>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>
                            {{ $editing ? 'Update' : 'Create' }} User
                        </button>
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-lg me-1"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-4">
            @if($editing)
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
                                <td>{{ $record->id }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Created:</td>
                                <td>{{ $record->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Updated:</td>
                                <td>{{ $record->updated_at->format('M d, Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($record->id !== auth()->id())
                    @can('delete users')
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
                    @endcan
                @endif
            @else
                <div class="card card-info card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="bi bi-info-circle me-2"></i>
                            Information
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small"><i class="bi bi-dot"></i> All fields marked with <span class="text-danger">*</span> are required.</p>
                        <p class="text-muted small"><i class="bi bi-dot"></i> Password must be at least 8 characters long.</p>
                        <p class="text-muted small mb-0"><i class="bi bi-dot"></i> Email address must be unique.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if($editing && $record->id !== auth()->id())
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
                        <p>Are you sure you want to delete <strong>{{ $record->name }}</strong>?</p>
                        <p class="text-muted small mb-0">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <form action="{{ route('users.destroy', $record) }}" method="POST" class="d-inline">
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

@push('scripts')
    <script>
        function initUserForm() {
            if (typeof $ !== 'undefined') {
                $(document).ready(function () {
                    $('select[data-toggle="select2"]').select2({
                        theme: 'bootstrap-5',
                        placeholder: $(this).data('placeholder'),
                        allowClear: $(this).data('allow-clear'),
                        width: '100%'
                    });

                    $('#deleteUserBtn').on('click', function () {
                        var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
                        deleteModal.show();
                    });
                });
            } else {
                setTimeout(initUserForm, 100);
            }
        }

        initUserForm();
    </script>
@endpush
@endsection
