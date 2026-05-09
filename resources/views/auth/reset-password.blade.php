@extends('components.authentication')

@section('title', 'Reset Password | HR Management System')

@section('content')
    <div class="auth-logo">
        <img src="{{ asset('app-assets/images/logo-light.png') }}" alt="HRMS Logo"
             onerror="this.style.display='none'; document.getElementById('authLogoFallback').style.display='inline-grid';">

        <span id="authLogoFallback" class="auth-logo-fallback" style="display: none;">
            <i class="mdi mdi-account-group-outline"></i>
        </span>
    </div>

    <div class="auth-header">
        <h1>Reset Password</h1>
        <p>Create a new secure password for your HR account.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <i class="mdi mdi-alert-circle-outline"></i>
            <div>{{ $errors->first() }}</div>
        </div>
    @endif

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="form-group">
            <label for="email" class="form-label">
                Email Address <span class="required">*</span>
            </label>

            <div class="input-wrap">
                <i class="mdi mdi-email-outline input-icon"></i>

                <input
                    id="email"
                    class="form-control"
                    type="email"
                    name="email"
                    value="{{ old('email', $request->email) }}"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="Enter your email address"
                >
            </div>

            @if ($errors->get('email'))
                <div style="margin-top: 8px; color: #dc2626; font-size: 13px;">
                    {{ $errors->first('email') }}
                </div>
            @endif
        </div>

        <div class="form-group">
            <label for="password" class="form-label">
                New Password <span class="required">*</span>
            </label>

            <div class="input-wrap">
                <i class="mdi mdi-lock-outline input-icon"></i>

                <input
                    id="password"
                    class="form-control"
                    type="password"
                    name="password"
                    required
                    autocomplete="new-password"
                    placeholder="Enter new password"
                >

                <button type="button" class="password-toggle" data-password-toggle="#password">
                    <i class="mdi mdi-eye-outline"></i>
                </button>
            </div>

            @if ($errors->get('password'))
                <div style="margin-top: 8px; color: #dc2626; font-size: 13px;">
                    {{ $errors->first('password') }}
                </div>
            @endif
        </div>

        <div class="form-group">
            <label for="password_confirmation" class="form-label">
                Confirm Password <span class="required">*</span>
            </label>

            <div class="input-wrap">
                <i class="mdi mdi-shield-lock-outline input-icon"></i>

                <input
                    id="password_confirmation"
                    class="form-control"
                    type="password"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="Confirm new password"
                >

                <button type="button" class="password-toggle" data-password-toggle="#password_confirmation">
                    <i class="mdi mdi-eye-outline"></i>
                </button>
            </div>

            @if ($errors->get('password_confirmation'))
                <div style="margin-top: 8px; color: #dc2626; font-size: 13px;">
                    {{ $errors->first('password_confirmation') }}
                </div>
            @endif
        </div>

        <button type="submit" class="btn-login">
            Reset Password
        </button>

        <div class="auth-divider">
            <span>secure password recovery</span>
        </div>

        <div style="text-align: center;">
            <a href="{{ route('login') }}" class="auth-link">
                Back to Login
            </a>
        </div>
    </form>
@endsection