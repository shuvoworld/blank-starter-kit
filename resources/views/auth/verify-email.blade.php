@extends('layouts.auth.app')

@section('content')
<div class="card">
    <div class="card-body login-card-body">
        <p class="login-box-msg">Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you?</p>

        @if (session('status') == 'verification-link-sent')
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-check"></i> Success!</h5>
                A new verification link has been sent to the email address you provided during registration.
            </div>
        @endif

        <div class="row mb-3">
            <div class="col-12">
                <form method="POST" action="{{ route('verification.send') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        Resend Verification Email
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}" class="d-inline ms-2">
                    @csrf
                    <button type="submit" class="btn btn-default">
                        Log Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
