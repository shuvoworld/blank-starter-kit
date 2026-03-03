@extends('layouts.auth.app')

@section('content')
<!-- Session Status -->
<x-auth-session-status class="mb-3" :status="session('status')" />

<div class="card">
    <div class="card-body register-card-body">
        <p class="login-box-msg">Register a new membership</p>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- Name -->
            <div class="input-group mb-3">
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="Full name" :value="old('name')" required autofocus autocomplete="name">
                <div class="input-group-text">
                    <span class="fas fa-user"></span>
                </div>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Email Address -->
            <div class="input-group mb-3">
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="Email" :value="old('email')" required autocomplete="username">
                <div class="input-group-text">
                    <span class="fas fa-envelope"></span>
                </div>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Password -->
            <div class="input-group mb-3">
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Password" required autocomplete="new-password">
                <div class="input-group-text">
                    <span class="fas fa-lock"></span>
                </div>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div class="input-group mb-3">
                <input type="password" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" placeholder="Retype password" required autocomplete="new-password">
                <div class="input-group-text">
                    <span class="fas fa-lock"></span>
                </div>
                @error('password_confirmation')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row">
                <div class="col-8">
                    <div class="icheck-primary">
                        <input type="checkbox" id="agree" name="agree" value="agree">
                        <label for="agree">
                            I agree to the <a href="#">terms</a>
                        </label>
                    </div>
                </div>
                <div class="col-4">
                    <button type="submit" class="btn btn-primary btn-block">
                        Register
                    </button>
                </div>
            </div>
        </form>

        <!-- Social Auth Links (Optional - can be enabled later) -->
        {{-- <div class="social-auth-links text-center">
            <p>- OR -</p>
            <a href="#" class="btn btn-block btn-primary">
                <i class="fab fa-facebook mr-2"></i> Sign up using Facebook
            </a>
            <a href="#" class="btn btn-block btn-danger">
                <i class="fab fa-google-plus mr-2"></i> Sign up using Google+
            </a>
        </div> --}}

        <!-- Login Link -->
        <a href="{{ route('login') }}" class="text-center">
            I already have a membership
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
  $(function () {
    $('input').iCheck({
      checkboxClass: 'icheckbox_square-blue',
      radioClass: 'iradio_square-blue',
      increaseArea: '20%'
    })
  })
</script>
@endpush
