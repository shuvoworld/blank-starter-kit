@extends('layouts.auth.app')

@section('content')
<div class="card">
    <div class="card-body login-card-body">
        <p class="login-box-msg">This is a secure area of the application. Please confirm your password before continuing.</p>

        <!-- Session Status -->
        <x-auth-session-status class="mb-3" :status="session('status')" />

        <form method="POST" action="{{ route('password.confirm') }}">
            @csrf

            <!-- Password -->
            <div class="input-group mb-3">
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Password" required autocomplete="current-password">
                <div class="input-group-text">
                    <span class="fas fa-lock"></span>
                </div>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary btn-block">
                        Confirm Password
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
