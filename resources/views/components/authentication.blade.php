<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'HR Management System')</title>

    <link rel="icon" type="image/png" href="{{ asset('app-assets/images/logo-light.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --text: #1e293b;
            --muted: #64748b;
            --border: #dbe3ef;
            --danger: #dc2626;
            --success: #16a34a;
            --card-bg: rgba(255, 255, 255, 0.94);
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            width: 100%;
            height: 100%;
            margin: 0;
            overflow: hidden;
        }

        body {
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(37, 99, 235, .10), transparent 28%),
                radial-gradient(circle at top right, rgba(37, 99, 235, .09), transparent 24%),
                radial-gradient(circle at bottom right, rgba(37, 99, 235, .10), transparent 28%),
                linear-gradient(135deg, #f8fbff 0%, #eef5ff 45%, #ffffff 100%);
            position: relative;
        }

        .auth-page {
            position: relative;
            width: 100%;
            height: 100dvh;
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            overflow: hidden;
        }

        /* soft background circles */
        .auth-page::before,
        .auth-page::after {
            content: "";
            position: absolute;
            border-radius: 999px;
            pointer-events: none;
            z-index: 0;
        }

        .auth-page::before {
            width: 380px;
            height: 380px;
            left: -140px;
            top: -120px;
            background: rgba(37, 99, 235, .05);
        }

        .auth-page::after {
            width: 430px;
            height: 430px;
            right: -170px;
            bottom: -180px;
            background: rgba(37, 99, 235, .06);
        }

        .dots-top-right,
        .dots-bottom-left {
            position: absolute;
            width: 96px;
            height: 96px;
            pointer-events: none;
            opacity: .7;
            z-index: 0;
            background-image: radial-gradient(circle, rgba(59, 130, 246, .22) 2px, transparent 2px);
            background-size: 16px 16px;
        }

        .dots-top-right {
            top: 34px;
            right: 34px;
        }

        .dots-bottom-left {
            left: 24px;
            bottom: 24px;
        }

        /* left illustration background */
        .auth-visual {
            position: absolute;
            left: 0;
            bottom: 0;
            width: min(31vw, 420px);
            z-index: 1;
            pointer-events: none;
            user-select: none;
        }

        .auth-visual img {
            display: block;
            width: 100%;
            height: auto;
            object-fit: contain;
            filter: drop-shadow(0 18px 40px rgba(15, 23, 42, 0.08));
        }

        .auth-center {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 610px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .auth-card {
            width: 100%;
            max-width: 520px;
            background: var(--card-bg);
            border: 1px solid rgba(219, 227, 239, .9);
            border-radius: 28px;
            padding: 42px 44px;
            box-shadow: 0 28px 80px rgba(15, 23, 42, .12);
            backdrop-filter: blur(16px);
        }

        .auth-logo {
            text-align: center;
            margin-bottom: 22px;
        }

        .auth-logo img {
            max-width: 82px;
            max-height: 82px;
            object-fit: contain;
            display: inline-block;
        }

        .auth-logo-fallback {
            width: 76px;
            height: 76px;
            border-radius: 24px;
            display: inline-grid;
            place-items: center;
            color: white;
            font-size: 40px;
            background: linear-gradient(135deg, #2563eb, #60a5fa);
            box-shadow: 0 20px 35px rgba(37, 99, 235, .25);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .auth-header h1 {
            margin: 0;
            font-size: 30px;
            font-weight: 800;
            letter-spacing: -.04em;
            color: #172033;
        }

        .auth-header p {
            margin: 9px 0 0;
            color: var(--muted);
            font-size: 15px;
        }

        .alert {
            border-radius: 16px;
            padding: 13px 15px;
            font-size: 14px;
            margin-bottom: 18px;
            display: flex;
            gap: 10px;
            align-items: flex-start;
        }

        .alert-danger {
            color: #991b1b;
            background: #fef2f2;
            border: 1px solid #fecaca;
        }

        .alert-success {
            color: #166534;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 700;
            color: #263246;
            margin-bottom: 9px;
        }

        .required {
            color: var(--danger);
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #8da0bd;
            font-size: 22px;
            pointer-events: none;
        }

        .form-control {
            width: 100%;
            height: 54px;
            border-radius: 14px;
            border: 1px solid var(--border);
            background: #fff;
            outline: none;
            padding: 0 46px 0 48px;
            font-size: 15px;
            color: var(--text);
            transition: .18s ease;
        }

        .form-control::placeholder {
            color: #94a3b8;
        }

        .form-control:focus {
            border-color: rgba(37, 99, 235, .75);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .12);
        }

        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            color: #7c8da8;
            font-size: 22px;
            cursor: pointer;
            width: 32px;
            height: 32px;
            border-radius: 10px;
        }

        .password-toggle:hover {
            background: #f1f5f9;
            color: var(--primary);
        }

        .auth-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            margin: 4px 0 24px;
        }

        .remember {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            color: #475569;
            font-size: 14px;
            user-select: none;
        }

        .remember input {
            width: 17px;
            height: 17px;
            accent-color: var(--primary);
        }

        .auth-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
        }

        .auth-link:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .btn-login {
            width: 100%;
            height: 56px;
            border: 0;
            border-radius: 15px;
            color: white;
            font-size: 16px;
            font-weight: 800;
            cursor: pointer;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            box-shadow: 0 18px 34px rgba(37, 99, 235, .28);
            transition: .18s ease;
        }

        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 22px 40px rgba(37, 99, 235, .34);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .secondary-button {
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
        }

        .secondary-button:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        .auth-divider {
            display: flex;
            align-items: center;
            gap: 16px;
            color: #94a3b8;
            font-size: 13px;
            margin: 28px 0 20px;
        }

        .auth-divider::before,
        .auth-divider::after {
            content: "";
            height: 1px;
            flex: 1;
            background: #e2e8f0;
        }

        .auth-footer-note {
            margin-top: 16px;
            text-align: center;
            color: #718096;
            font-size: 13px;
        }

        @media (max-width: 1100px) {
            .auth-visual {
                width: min(28vw, 340px);
            }
        }

        @media (max-width: 920px) {
            .auth-visual {
                display: none;
            }

            .auth-center {
                max-width: 520px;
            }
        }

        @media (max-width: 640px) {
            html,
            body {
                overflow: hidden;
            }

            .auth-page {
                padding: 18px;
            }

            .auth-card {
                max-width: 100%;
                padding: 30px 22px;
                border-radius: 24px;
            }

            .auth-header h1 {
                font-size: 24px;
            }

            .auth-row {
                align-items: flex-start;
                flex-direction: column;
            }

            .auth-logo img {
                max-width: 68px;
                max-height: 68px;
            }
        }
    </style>

    @stack('styles')
</head>
<body>
    <main class="auth-page">
        <div class="dots-top-right"></div>
        <div class="dots-bottom-left"></div>

        <div class="auth-visual" aria-hidden="true">
            <img
                src="{{ asset('app-assets/images/auth/auth-people-bg.png') }}"
                alt=""
                onerror="this.parentElement.style.display='none';"
            >
        </div>

        <section class="auth-center">
            <div class="auth-card">
                @yield('content')
            </div>

            <div class="auth-footer-note">
                &copy; {{ date('Y') }} Smart Pharmacy. All rights reserved.
            </div>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
                button.addEventListener('click', function () {
                    const target = document.querySelector(button.getAttribute('data-password-toggle'));

                    if (!target) return;

                    const isPassword = target.getAttribute('type') === 'password';
                    target.setAttribute('type', isPassword ? 'text' : 'password');

                    const icon = button.querySelector('i');

                    if (icon) {
                        icon.className = isPassword ? 'mdi mdi-eye-off-outline' : 'mdi mdi-eye-outline';
                    }
                });
            });
        });
    </script>

    @stack('scripts')
</body>
</html>