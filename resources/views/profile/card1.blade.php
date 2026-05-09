@php
    $displayName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));

    if ($displayName === '') {
        $displayName = $user->name ?? $user->username ?? 'Ricky Kinyamagoha';
    }

    $roleName = $user->roles->pluck('name')->first() ?? 'Managing Pharmacist';

    $phone = $branch?->phone
        ?? $pharmacy?->phone
        ?? $user->phone
        ?? $user->phone_number
        ?? '+255 624 592 725';

    $email = $pharmacy?->email
        ?? $user->email
        ?? 'info@smartpharmacy.co.tz';

    $website = $settings?->website
        ?? $pharmacy?->website
        ?? 'www.smartpharmacy.co.tz';

    $address = $branch?->address
        ?? $pharmacy?->address
        ?? 'Dar es Salaam, Tanzania';

    $primaryColor = $primaryColor ?? '#071f3c';
    $secondaryColor = $secondaryColor ?? '#d3a34d';

    $blue = '#0570c9';
    $green = '#5a9d37';
    $textBlue = '#0b3768';
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Business Card</title>

    <style>
        @page {
            margin: 0;
            size: 252pt 144pt;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            width: 252pt;
            height: 144pt;
            font-family: DejaVu Sans, Arial, sans-serif;
            background: #ffffff;
        }

        .page {
            width: 252pt;
            height: 144pt;
            position: relative;
            overflow: hidden;
            page-break-after: always;
            background: #ffffff;
        }

        .page:last-child {
            page-break-after: auto;
        }

        .logo-mark {
            width: 30pt;
            height: 31pt;
            position: relative;
            margin: 0 auto 2pt auto;
        }

        .cross-v {
            position: absolute;
            left: 11pt;
            top: 0;
            width: 10.5pt;
            height: 25pt;
            background: {{ $blue }};
            border-radius: 4pt;
        }

        .cross-h {
            position: absolute;
            left: 3pt;
            top: 8pt;
            width: 28pt;
            height: 10.5pt;
            background: {{ $blue }};
            border-radius: 4pt;
        }

        .leaf {
            position: absolute;
            right: -1pt;
            top: 6pt;
            width: 17.5pt;
            height: 14pt;
            background: {{ $green }};
            border-radius: 0 13pt 0 13pt;
            transform: rotate(-18deg);
        }

        .leaf:after {
            content: "";
            position: absolute;
            left: 4pt;
            top: 6pt;
            width: 12pt;
            height: 1pt;
            background: #ffffff;
            transform: rotate(-24deg);
        }

        .capsule {
            position: absolute;
            left: 10pt;
            top: 14pt;
            width: 12.5pt;
            height: 21.5pt;
            border: 1.8pt solid #ffffff;
            border-radius: 9pt;
            transform: rotate(28deg);
            background: {{ $blue }};
        }

        .capsule:after {
            content: "";
            position: absolute;
            left: 0;
            top: 9pt;
            width: 100%;
            height: 1pt;
            background: #ffffff;
        }

        .brand-smart {
            font-size: 18pt;
            line-height: 15pt;
            font-weight: 900;
            color: {{ $textBlue }};
            letter-spacing: -0.8pt;
        }

        .brand-pharmacy {
            font-size: 13.5pt;
            line-height: 14pt;
            font-weight: 400;
            color: {{ $textBlue }};
        }

        /*
        |--------------------------------------------------------------------------
        | FRONT SIDE
        |--------------------------------------------------------------------------
        */

        .front-navy {
            position: absolute;
            right: 0;
            top: 0;
            width: 160pt;
            height: 144pt;
            background: {{ $primaryColor }};
            z-index: 1;
        }

        .front-white {
            position: absolute;
            left: -38pt;
            top: -20pt;
            width: 136pt;
            height: 185pt;
            background: #ffffff;
            border-top-right-radius: 96pt;
            border-bottom-right-radius: 96pt;
            z-index: 5;
        }

        .front-gold-divider {
            position: absolute;
            left: 98pt;
            top: -22pt;
            width: 2.8pt;
            height: 188pt;
            background: {{ $secondaryColor }};
            transform: rotate(-12deg);
            z-index: 4;
        }

        .front-bottom-gold {
            position: absolute;
            right: -45pt;
            bottom: -62pt;
            width: 205pt;
            height: 90pt;
            border-top: 3.8pt solid {{ $secondaryColor }};
            border-top-left-radius: 195pt;
            transform: rotate(-5deg);
            z-index: 2;
        }

        .front-logo {
            position: absolute;
            left: 19pt;
            top: 35pt;
            width: 72pt;
            text-align: center;
            z-index: 10;
        }

        .front-logo .brand-smart {
            font-size: 18pt;
            line-height: 15pt;
        }

        .front-logo .brand-pharmacy {
            font-size: 13.5pt;
            line-height: 14pt;
        }

        .front-tagline {
            margin-top: 5pt;
            font-size: 5.4pt;
            line-height: 7pt;
            letter-spacing: 1.05pt;
            color: #0b5595;
            white-space: nowrap;
        }

        .front-tagline span {
            color: {{ $green }};
            padding: 0 1.3pt;
        }

        .front-info {
            position: absolute;
            left: 132pt;
            top: 31pt;
            width: 110pt;
            color: #ffffff;
            z-index: 20;
        }

        .person-name {
            font-size: 10.6pt;
            line-height: 12pt;
            font-weight: 900;
            color: {{ $secondaryColor }};
            white-space: nowrap;
        }

        .person-title {
            margin-top: 2pt;
            font-size: 7.4pt;
            line-height: 9pt;
            color: #ffffff;
            white-space: nowrap;
        }

        .front-small-line {
            width: 36pt;
            height: 1.1pt;
            background: {{ $secondaryColor }};
            margin-top: 8pt;
            margin-bottom: 8pt;
        }

        .contact-row {
            display: table;
            width: 100%;
            margin-bottom: 4.8pt;
        }

        .contact-icon-cell {
            display: table-cell;
            width: 16pt;
            vertical-align: middle;
        }

        .contact-icon {
            width: 11pt;
            height: 11pt;
            border-radius: 50%;
            background: {{ $secondaryColor }};
            color: {{ $primaryColor }};
            text-align: center;
            font-size: 5.5pt;
            line-height: 11pt;
            font-weight: 900;
        }

        .contact-text {
            display: table-cell;
            vertical-align: middle;
            color: #ffffff;
            font-size: 6.3pt;
            line-height: 7.6pt;
            white-space: nowrap;
        }

        /*
        |--------------------------------------------------------------------------
        | BACK SIDE - FIXED ABSOLUTE CONTACTS
        |--------------------------------------------------------------------------
        */

        .back-top-line-one,
        .back-top-line-two,
        .back-top-line-three {
            position: absolute;
            right: -48pt;
            top: -38pt;
            border: .45pt solid #d8dee8;
            border-radius: 50%;
            z-index: 1;
        }

        .back-top-line-one {
            width: 128pt;
            height: 90pt;
        }

        .back-top-line-two {
            width: 113pt;
            height: 80pt;
            right: -41pt;
            top: -31pt;
        }

        .back-top-line-three {
            width: 98pt;
            height: 70pt;
            right: -34pt;
            top: -24pt;
        }

        .back-logo {
            position: absolute;
            left: 0;
            top: 14pt;
            width: 252pt;
            text-align: center;
            z-index: 10;
        }

        .back-logo .logo-mark {
            width: 31pt;
            height: 31pt;
            margin-bottom: 1pt;
        }

        .back-logo .cross-v {
            left: 12pt;
            width: 10pt;
            height: 26pt;
        }

        .back-logo .cross-h {
            left: 3.5pt;
            top: 8.5pt;
            width: 28pt;
            height: 10pt;
        }

        .back-logo .leaf {
            width: 18pt;
            height: 14pt;
        }

        .back-logo .capsule {
            left: 11.5pt;
            top: 15pt;
            width: 12.5pt;
            height: 21.5pt;
        }

        .back-logo .brand-smart {
            font-size: 19.5pt;
            line-height: 16pt;
        }

        .back-logo .brand-pharmacy {
            font-size: 14.5pt;
            line-height: 15pt;
        }

        .back-gold-line {
            width: 56pt;
            height: 1.1pt;
            background: {{ $secondaryColor }};
            margin: 4pt auto 4pt auto;
        }

        .back-tagline {
            font-size: 5.8pt;
            line-height: 7pt;
            color: {{ $textBlue }};
            font-weight: 700;
        }

        .back-footer-area {
            position: absolute;
            left: 0;
            bottom: 23pt;
            width: 252pt;
            height: 18pt;
            z-index: 50;
            background: #ffffff;
        }

        .back-footer-item {
            position: absolute;
            top: 0;
            height: 18pt;
            text-align: center;
            overflow: hidden;
            color: #1f2937;
        }

        .back-footer-item.item-phone {
            left: 15pt;
            width: 45pt;
        }

        .back-footer-item.item-email {
            left: 64pt;
            width: 55pt;
        }

        .back-footer-item.item-website {
            left: 123pt;
            width: 64pt;
        }

        .back-footer-item.item-address {
            left: 191pt;
            width: 48pt;
        }

        .back-footer-divider {
            position: absolute;
            top: 2pt;
            width: .8pt;
            height: 14pt;
            background: {{ $secondaryColor }};
        }

        .divider-one {
            left: 61pt;
        }

        .divider-two {
            left: 120pt;
        }

        .divider-three {
            left: 188pt;
        }

        .back-footer-icon {
            color: {{ $textBlue }};
            font-size: 7.4pt;
            line-height: 7pt;
            font-weight: 900;
            margin-bottom: 1pt;
        }

        .back-footer-text {
            color: #1f2937;
            font-size: 3.8pt;
            line-height: 4.4pt;
            white-space: nowrap;
        }

        .back-gold-curve {
            position: absolute;
            left: -22pt;
            bottom: 15pt;
            width: 296pt;
            height: 26pt;
            border-bottom: 4pt solid {{ $secondaryColor }};
            border-bottom-left-radius: 155pt;
            border-bottom-right-radius: 155pt;
            z-index: 3;
        }

        .back-navy-bottom {
            position: absolute;
            left: -22pt;
            bottom: -39pt;
            width: 296pt;
            height: 55pt;
            background: {{ $primaryColor }};
            border-top-left-radius: 155pt;
            border-top-right-radius: 155pt;
            z-index: 1;
        }
    </style>
</head>

<body>

    {{-- PAGE 1: FRONT --}}
    <div class="page">
        <div class="front-navy"></div>
        <div class="front-gold-divider"></div>
        <div class="front-white"></div>
        <div class="front-bottom-gold"></div>

        <div class="front-logo">
            <div class="logo-mark">
                <div class="cross-v"></div>
                <div class="cross-h"></div>
                <div class="leaf"></div>
                <div class="capsule"></div>
            </div>

            <div class="brand-smart">Smart</div>
            <div class="brand-pharmacy">Pharmacy</div>

            <div class="front-tagline">
                Health <span>•</span> Care <span>•</span> Trust
            </div>
        </div>

        <div class="front-info">
            <div class="person-name">{{ $displayName }}</div>
            <div class="person-title">{{ $roleName }}</div>

            <div class="front-small-line"></div>

            <div class="contact-row">
                <div class="contact-icon-cell">
                    <div class="contact-icon">T</div>
                </div>
                <div class="contact-text">{{ $phone }}</div>
            </div>

            <div class="contact-row">
                <div class="contact-icon-cell">
                    <div class="contact-icon">M</div>
                </div>
                <div class="contact-text">{{ $email }}</div>
            </div>

            <div class="contact-row">
                <div class="contact-icon-cell">
                    <div class="contact-icon">W</div>
                </div>
                <div class="contact-text">{{ $website }}</div>
            </div>

            <div class="contact-row">
                <div class="contact-icon-cell">
                    <div class="contact-icon">L</div>
                </div>
                <div class="contact-text">{{ $address }}</div>
            </div>
        </div>
    </div>

    {{-- PAGE 2: BACK --}}
    <div class="page">
        <div class="back-top-line-one"></div>
        <div class="back-top-line-two"></div>
        <div class="back-top-line-three"></div>

        <div class="back-logo">
            <div class="logo-mark">
                <div class="cross-v"></div>
                <div class="cross-h"></div>
                <div class="leaf"></div>
                <div class="capsule"></div>
            </div>

            <div class="brand-smart">Smart</div>
            <div class="brand-pharmacy">Pharmacy</div>

            <div class="back-gold-line"></div>

            <div class="back-tagline">
                Reliable medicine. Trusted care.
            </div>
        </div>

        <div class="back-footer-area">
            <div class="back-footer-item item-phone">
                <div class="back-footer-icon">T</div>
                <div class="back-footer-text">{{ $phone }}</div>
            </div>

            <div class="back-footer-divider divider-one"></div>

            <div class="back-footer-item item-email">
                <div class="back-footer-icon">M</div>
                <div class="back-footer-text">{{ $email }}</div>
            </div>

            <div class="back-footer-divider divider-two"></div>

            <div class="back-footer-item item-website">
                <div class="back-footer-icon">W</div>
                <div class="back-footer-text">{{ $website }}</div>
            </div>

            <div class="back-footer-divider divider-three"></div>

            <div class="back-footer-item item-address">
                <div class="back-footer-icon">L</div>
                <div class="back-footer-text">{{ $address }}</div>
            </div>
        </div>

        <div class="back-gold-curve"></div>
        <div class="back-navy-bottom"></div>
    </div>

</body>
</html>