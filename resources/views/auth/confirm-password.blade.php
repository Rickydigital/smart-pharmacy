@extends('components.authentication')

@section('title', 'Confirm Password | HR Management System')

@section('content')
    <div class="auth-logo">
        <img src="{{ asset('app-assets/images/logo-light.png') }}" alt="HRMS Logo"
             onerror="this.style.display='none'; document.getElementById('authLogoFallback').style.display='inline-grid';">

        <span id="authLogoFallback" class="auth-logo-fallback" style="display: none;">
            <i class="mdi mdi-account-group-outline"></i>
        </span>
    </div>

    <div class="auth-header">
        <h1>Confirm Password</h1>
        <p>This is a secure area. Please confirm your password before continuing.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <i class="mdi mdi-alert-circle-outline"></i>
            <div>{{ $errors->first() }}</div>
        </div>
    @endif

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="form-group">
            <label for="password" class="form-label">
                Password <span class="required">*</span>
            </label>

            <div class="input-wrap">
                <i class="mdi mdi-lock-outline input-icon"></i>

                <input
                    id="password"
                    class="form-control"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="Enter your password"
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

        <button type="submit" class="btn-login">
            Confirm Password
        </button>

        <div class="auth-divider">
            <span>protected access</span>
        </div>

        <div style="text-align: center;">
            <a href="{{ route('dashboard') }}" class="auth-link">
                Back to Dashboard
            </a>
        </div>
    </form>
@endsection