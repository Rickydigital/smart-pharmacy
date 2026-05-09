<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ config('app.name', 'Smart Pharmacy') }} | @yield('title', 'POS')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" href="{{ asset('app-assets/images/logo-sm.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <link href="{{ asset('app-assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('app-assets/css/icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('app-assets/css/app.min.css') }}" rel="stylesheet">
    <link href="{{ asset('app-assets/select2/css/select2.min.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font/css/materialdesignicons.min.css" rel="stylesheet">

    <style>
        :root {
            --pos-primary: #155dfc;
            --pos-primary-dark: #0f3fbf;
            --pos-soft: #eff6ff;
            --pos-bg: #f5f8fd;
            --pos-line: #e2e8f0;
            --pos-text: #0f172a;
            --pos-muted: #64748b;
            --pos-radius: 20px;
            --pos-shadow: 0 18px 45px rgba(15, 23, 42, .08);
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            min-height: 100%;
            margin: 0;
            font-family: 'Inter', sans-serif;
            background:
                radial-gradient(circle at 90% 0%, rgba(37, 99, 235, .08), transparent 26%),
                linear-gradient(180deg, #f8fbff 0%, #f5f8fd 100%);
            color: var(--pos-text);
            overflow-x: hidden;
        }

        .pos-shell {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .pos-main {
            flex: 1 1 auto;
            padding: 24px 28px;
        }

        .pos-topbar {
            height: 86px;
            padding: 0 28px;
            background: rgba(255, 255, 255, .96);
            border-bottom: 1px solid rgba(226, 232, 240, .95);
            box-shadow: 0 10px 28px rgba(15, 23, 42, .05);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            position: sticky;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(16px);
        }

        .pos-brand-area {
            display: flex;
            align-items: center;
            gap: 18px;
            min-width: 0;
        }

        .pos-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-right: 24px;
            border-right: 1px solid #e5e7eb;
        }

        .pos-logo-mark {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            background: linear-gradient(135deg, #155dfc, #38bdf8);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 25px;
            box-shadow: 0 13px 24px rgba(37, 99, 235, .22);
        }

        .pos-brand-text strong {
            display: block;
            color: #0f172a;
            font-size: 22px;
            font-weight: 950;
            line-height: 1;
            letter-spacing: -.04em;
        }

        .pos-brand-text span {
            display: block;
            color: #64748b;
            font-size: 10px;
            font-weight: 900;
            letter-spacing: .28em;
            margin-top: 4px;
        }

        .pos-page-title {
            font-size: 22px;
            font-weight: 950;
            color: #0f172a;
            letter-spacing: -.035em;
            white-space: nowrap;
        }

        .pos-topbar-actions {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
        }

        .pos-top-card {
            height: 54px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            border-radius: 16px;
            padding: 8px 14px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            min-width: 180px;
            box-shadow: 0 8px 20px rgba(15, 23, 42, .04);
        }

        .pos-top-icon {
            width: 34px;
            height: 34px;
            border-radius: 12px;
            background: var(--pos-soft);
            color: var(--pos-primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex: 0 0 auto;
        }

        .pos-top-meta {
            line-height: 1.1;
            min-width: 0;
        }

        .pos-top-meta small {
            display: block;
            color: #64748b;
            font-size: 11px;
            font-weight: 700;
        }

        .pos-top-meta strong {
            display: block;
            color: #0f172a;
            font-size: 13px;
            font-weight: 900;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .pos-branch-select {
            border: 0;
            outline: 0;
            color: #0f172a;
            font-size: 13px;
            font-weight: 900;
            background: transparent;
            min-width: 120px;
            padding: 0;
        }

        .pos-back-link {
            height: 42px;
            width: 42px;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            color: #334155;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .pos-back-link:hover {
            background: var(--pos-soft);
            color: var(--pos-primary);
        }

        .pos-alert {
            border: 0;
            border-radius: 16px;
            font-size: 13px;
            font-weight: 700;
        }

        @media (max-width: 991.98px) {
            .pos-topbar {
                height: auto;
                min-height: 82px;
                padding: 14px;
                align-items: flex-start;
                flex-direction: column;
            }

            .pos-brand-area {
                width: 100%;
                justify-content: space-between;
            }

            .pos-brand {
                border-right: 0;
                padding-right: 0;
            }

            .pos-page-title {
                display: none;
            }

            .pos-topbar-actions {
                width: 100%;
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 10px;
            }

            .pos-top-card {
                min-width: 0;
                width: 100%;
            }

            .pos-main {
                padding: 14px;
            }
        }

        @media (max-width: 575.98px) {
            .pos-brand-text strong {
                font-size: 18px;
            }

            .pos-topbar-actions {
                grid-template-columns: 1fr;
            }

            .pos-top-card {
                height: 50px;
            }
        }
    </style>

    @stack('styles')
</head>

<body>
<div class="pos-shell">
    @include('components.pos-topbar')

    <main class="pos-main">
        @if (session('success'))
            <div class="alert alert-success pos-alert mb-3">
                <i class="mdi mdi-check-circle-outline mr-1"></i>
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger pos-alert mb-3">
                <i class="mdi mdi-alert-circle-outline mr-1"></i>
                {{ $errors->first() }}
            </div>
        @endif

        @yield('content')
    </main>
</div>

<script src="{{ asset('app-assets/js/vendor.min.js') }}"></script>
<script src="{{ asset('app-assets/bootstrap-5.0.2/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('app-assets/select2/js/select2.full.min.js') }}"></script>

@stack('scripts')
</body>
</html>