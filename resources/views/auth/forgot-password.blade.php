@extends('components.authentication')

@section('title', 'Forgot Password | HR Management System')

@section('content')
    <div class="auth-logo">
        <img src="{{ asset('app-assets/images/logo-light.png') }}" alt="HRMS Logo"
             onerror="this.style.display='none'; document.getElementById('authLogoFallback').style.display='inline-grid';">

        <span id="authLogoFallback" class="auth-logo-fallback" style="display: none;">
            <i class="mdi mdi-account-group-outline"></i>
        </span>
    </div>

    <div class="auth-header">
        <h1>Forgot Password?</h1>
        <p>Enter your email address and we will send you a password reset link.</p>
    </div>

    @if (session('status'))
        <div class="alert alert-success">
            <i class="mdi mdi-check-circle-outline"></i>
            <div>{{ session('status') }}</div>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <i class="mdi mdi-alert-circle-outline"></i>
            <div>{{ $errors->first() }}</div>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

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
                    value="{{ old('email') }}"
                    required
                    autofocus
                    placeholder="Enter your email address"
                >
            </div>

            @if ($errors->get('email'))
                <div style="margin-top: 8px; color: #dc2626; font-size: 13px;">
                    {{ $errors->first('email') }}
                </div>
            @endif
        </div>

        <button type="submit" class="btn-login">
            Send Reset Link
        </button>

        <div class="auth-divider">
            <span>remembered password?</span>
        </div>

        <div style="text-align: center;">
            <a href="{{ route('login') }}" class="auth-link">
                Back to Login
            </a>
        </div>
    </form>
@endsection