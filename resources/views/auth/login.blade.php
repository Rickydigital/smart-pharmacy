@extends('components.authentication')

@section('title', 'Login | Smart Pharmacy')

@section('content')
    <div class="auth-logo">
        <img src="{{ asset('app-assets/images/logo-light.png') }}" alt="Smart Pharmacy Logo"
             onerror="this.style.display='none'; document.getElementById('authLogoFallback').style.display='inline-grid';">

        <span id="authLogoFallback" class="auth-logo-fallback" style="display: none;">
            <i class="mdi mdi-medical-bag"></i>
        </span>
    </div>

    <div class="auth-header">
        <h1>Smart Pharmacy</h1>
        <p>Sign in with your email or username</p>
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

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="form-group">
            <label for="login" class="form-label">
                Email or Username <span class="required">*</span>
            </label>

            <div class="input-wrap">
                <i class="mdi mdi-account-circle-outline input-icon"></i>

                <input
                    id="login"
                    class="form-control"
                    type="text"
                    name="login"
                    value="{{ old('login') }}"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="Enter email or username"
                >
            </div>

            @error('login')
                <div style="margin-top: 8px; color: #dc2626; font-size: 13px;">
                    {{ $message }}
                </div>
            @enderror
        </div>

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

            @error('password')
                <div style="margin-top: 8px; color: #dc2626; font-size: 13px;">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="auth-row">
            <label for="remember_me" class="remember">
                <input
                    id="remember_me"
                    type="checkbox"
                    name="remember"
                    {{ old('remember') ? 'checked' : '' }}
                >
                <span>Remember me</span>
            </label>

            @if (Route::has('password.request'))
                <a class="auth-link" href="{{ route('password.request') }}">
                    Forgot password?
                </a>
            @endif
        </div>

        <button type="submit" class="btn-login">
            Log in
        </button>

        <div class="auth-divider">
            <span>secure pharmacy access</span>
        </div>
    </form>
@endsection