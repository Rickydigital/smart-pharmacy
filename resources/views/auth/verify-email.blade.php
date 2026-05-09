@extends('components.authentication')

@section('title', 'Verify Email | HR Management System')

@section('content')
    <div class="auth-logo">
        <img src="{{ asset('app-assets/images/logo-light.png') }}" alt="HRMS Logo"
             onerror="this.style.display='none'; document.getElementById('authLogoFallback').style.display='inline-grid';">

        <span id="authLogoFallback" class="auth-logo-fallback" style="display: none;">
            <i class="mdi mdi-account-group-outline"></i>
        </span>
    </div>

    <div class="auth-header">
        <h1>Verify Your Email</h1>
        <p>
            Before continuing, please verify your email address by clicking the link
            we sent to your inbox.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success">
            <i class="mdi mdi-check-circle-outline"></i>
            <div>
                A new verification link has been sent to the email address you provided.
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <i class="mdi mdi-alert-circle-outline"></i>
            <div>{{ $errors->first() }}</div>
        </div>
    @endif

    <div style="
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 18px;
        margin-bottom: 22px;
        color: #475569;
        font-size: 14px;
        line-height: 1.7;
    ">
        <strong style="color: #1e293b;">Didn’t receive the email?</strong><br>
        Check your spam folder or request another verification link below.
    </div>

    <div style="display: grid; gap: 12px;">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <button type="submit" class="btn-login">
                Resend Verification Email
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" style="
                width: 100%;
                height: 52px;
                border-radius: 15px;
                border: 1px solid #dbe3ef;
                background: #ffffff;
                color: #475569;
                font-size: 15px;
                font-weight: 800;
                cursor: pointer;
                transition: .18s ease;
            ">
                Log Out
            </button>
        </form>
    </div>

    <div class="auth-divider">
        <span>email verification required</span>
    </div>

@endsection