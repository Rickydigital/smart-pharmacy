@extends('components.main-layout')

@section('title', 'Business Card')
@section('page-title', 'Business Card')
@section('page-kicker', 'Profile')
@section('page-subtitle', 'Choose and print your pharmacy business card.')

@section('content')
<style>
.card-page{max-width:1180px;margin:0 auto}
.card-toolbar{background:#fff;border:1px solid #e5edf7;border-radius:22px;padding:18px;box-shadow:0 18px 45px rgba(15,23,42,.06);display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:18px}
.card-toolbar h4{margin:0;font-weight:950;color:#0f172a;letter-spacing:-.03em}
.card-toolbar p{margin:4px 0 0;color:#64748b;font-weight:700;font-size:13px}
.card-actions{display:flex;gap:10px;flex-wrap:wrap}
.card-btn{height:42px;border-radius:14px;font-weight:900;padding:0 16px;border:1px solid #dbeafe;background:#fff;color:#2563eb}
.card-btn-dark{height:42px;border-radius:14px;font-weight:900;padding:0 16px;border:0;background:#0f172a;color:#fff}
.card-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:18px}
.card-choice{background:#fff;border:2px solid #e5edf7;border-radius:26px;padding:16px;box-shadow:0 18px 45px rgba(15,23,42,.06)}
.card-choice.active{border-color:#2563eb;background:#eff6ff}
.card-choice-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px}
.card-choice-head strong{font-weight:950;color:#0f172a}
.card-select-link{height:34px;border-radius:12px;border:0;background:#2563eb;color:#fff;font-weight:900;padding:0 12px;display:inline-flex;align-items:center;text-decoration:none}
.mockup{background:#101827;border-radius:22px;padding:18px;display:grid;gap:14px;box-shadow:inset 0 0 0 1px rgba(255,255,255,.06)}
.bc{width:100%;aspect-ratio:1.75/1;border-radius:22px;position:relative;overflow:hidden;padding:22px;box-sizing:border-box;box-shadow:0 18px 35px rgba(0,0,0,.22);font-family:Arial,sans-serif}
.bc-front{display:grid;grid-template-columns:43% 57%;height:100%;position:relative;z-index:3}
.bc-back{display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;position:relative;z-index:3}
.logo-row{display:flex;align-items:center;gap:10px}
.logo-box{width:48px;height:48px;border-radius:15px;background:#fff;display:flex;align-items:center;justify-content:center;overflow:hidden;color:#2563eb;font-size:28px}
.logo-box img{width:38px;height:38px;object-fit:contain}
.brand{font-size:27px;font-weight:950;line-height:.92;color:#0b3a75}
.brand small{display:block;font-size:21px;font-weight:500}
.tag{margin-top:10px;font-size:12px;font-weight:750;letter-spacing:2px;color:#0b4f95}
.person{font-size:21px;font-weight:950;line-height:1.1}
.role{font-size:13px;font-weight:700;margin-top:5px}
.line{width:42px;height:2px;margin:10px 0 16px}
.info{display:grid;gap:8px;font-size:12px;font-weight:700;line-height:1.25}
.info div{display:flex;align-items:center;gap:8px}
.info i{width:24px;height:24px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:14px;flex:0 0 auto}
.bottom-info{position:absolute;left:32px;right:32px;bottom:18px;display:grid;grid-template-columns:repeat(4,1fr);gap:8px;font-size:10px;font-weight:700;z-index:5;text-align:center}
.bottom-info div{border-right:1px solid currentColor;padding-right:7px}
.bottom-info div:last-child{border-right:0}
.card1.front{background:linear-gradient(90deg,#fff 0%,#fff 43%,#0c1b2f 43%,#132b48 100%);color:#fff}
.card1.front:before{content:"";position:absolute;left:38%;top:-20%;width:110px;height:140%;background:#fff;border-right:4px solid #d6ad55;border-radius:0 90px 90px 0}
.card1.front:after{content:"";position:absolute;right:-25px;bottom:-38px;width:180px;height:100px;border-top:5px solid #d6ad55;border-radius:999px;transform:rotate(-18deg)}
.card1 .left{color:#0f172a;display:flex;flex-direction:column;justify-content:center}
.card1 .right{padding-left:34px;display:flex;flex-direction:column;justify-content:center}
.card1 .person{color:#f3c76f}.card1 .role{color:#fff}.card1 .line{background:#d6ad55}.card1 .info i{background:#d6ad55;color:#0f172a}
.card1.back{background:#fff;color:#0f172a;border-bottom:16px solid #132b48}
.card1.back:after{content:"";position:absolute;left:0;right:0;bottom:10px;height:3px;background:#d6ad55}
.card2.front{background:#fff;color:#0f172a}
.card2.front:after{content:"";position:absolute;right:-30px;top:0;width:165px;height:100%;background:linear-gradient(135deg,#1d4ed8,#0ea5e9);border-radius:90px 0 0 90px}
.card2 .left{display:flex;flex-direction:column;justify-content:center;border-right:2px solid #1d4ed8}
.card2 .right{padding-left:34px;display:flex;flex-direction:column;justify-content:center}
.card2 .person{color:#0b3a75}.card2 .role{color:#1d4ed8}.card2 .line{background:#1d4ed8}.card2 .info i{background:#0b3a75;color:#fff}
.card2.back{background:#fff;color:#0f172a}
.card2.back:after{content:"";position:absolute;right:-20px;bottom:-40px;width:220px;height:95px;background:linear-gradient(135deg,#1d4ed8,#0ea5e9);border-radius:999px}
.card3.front{background:#fff;color:#0f172a}
.card3.front:after{content:"";position:absolute;right:-20px;top:0;width:145px;height:100%;background:#3a8f6b;border-radius:90px 0 0 90px}
.card3.front:before{content:"";position:absolute;right:40px;top:28px;width:60px;height:60px;border:2px solid rgba(255,255,255,.25);border-radius:18px}
.card3 .left{display:flex;flex-direction:column;justify-content:center;border-right:2px solid #1d4ed8}
.card3 .right{padding-left:34px;display:flex;flex-direction:column;justify-content:center}
.card3 .person{color:#0b3a75}.card3 .role{color:#1d4ed8}.card3 .line{background:#1d4ed8}.card3 .info i{background:#0b3a75;color:#fff}
.card3.back{background:#fff;color:#0f172a;border-bottom:13px solid #3a8f6b}
.card4.front{background:#fff;color:#0f172a}
.card4.front:after{content:"";position:absolute;right:-20px;top:0;width:155px;height:100%;background:linear-gradient(135deg,#60a5fa,#0284c7);border-radius:90px 0 0 90px}
.card4.front:before{content:"";position:absolute;left:-60px;bottom:-80px;width:230px;height:160px;background:rgba(14,165,233,.12);border-radius:999px}
.card4 .left{display:flex;flex-direction:column;justify-content:center;border-right:2px solid #1d4ed8}
.card4 .right{padding-left:34px;display:flex;flex-direction:column;justify-content:center}
.card4 .person{color:#0b3a75}.card4 .role{color:#1d4ed8}.card4 .line{background:#1d4ed8}.card4 .info i{background:#0b3a75;color:#fff}
.card4.back{background:#fff;color:#0f172a}
.card4.back:after{content:"";position:absolute;right:-30px;bottom:-42px;width:240px;height:100px;background:linear-gradient(135deg,#60a5fa,#0284c7);border-radius:999px}
.back-logo .logo-box{margin:0 auto 10px;width:58px;height:58px}
.back-logo .brand{font-size:34px}
.back-logo .brand small{font-size:25px}
.back-text{font-size:13px;font-weight:800;color:#0b4f95;margin-top:8px}
.print-only{display:none}
@media(max-width:850px){.card-grid{grid-template-columns:1fr}.card-toolbar{flex-direction:column;align-items:flex-start}.card-actions,.card-actions a{width:100%}.card-actions a{justify-content:center;display:flex}.bc{padding:16px}.person{font-size:17px}.brand{font-size:22px}.brand small{font-size:17px}.info{font-size:10px}.bottom-info{display:none}}
@media print{
body *{visibility:hidden!important}
.print-only,.print-only *{visibility:visible!important}
.print-only{display:block!important;position:absolute;left:0;top:0;width:100%;background:#fff;padding-top:18mm}
.print-sheet{display:grid;gap:10mm;justify-content:center}
.print-sheet .bc{width:88mm;height:54mm;border-radius:4mm;box-shadow:none;padding:7mm}
@page{size:A4 portrait;margin:0}
}
</style>

@php
    $displayName = $user->full_name ?: ($user->username ?: $user->email);
    $roleName = $user->roles?->first()?->name ?? 'Managing Pharmacist';
    $pharmacy = $user->pharmacy;
    $brandName = $pharmacy?->name ?: 'Smart Pharmacy';
    $brandPhone = $pharmacy?->phone ?: ($user->phone ?: '+255 624 592 725');
    $brandEmail = $pharmacy?->email ?: $user->email;
    $brandAddress = $pharmacy?->address ?: 'Dar es Salaam, Tanzania';
    $website = config('app.url') ? parse_url(config('app.url'), PHP_URL_HOST) : 'www.smartpharmacy.co.tz';
    $logo = $pharmacy?->logo_path ? asset('storage/' . ltrim($pharmacy->logo_path, '/')) : null;

    $cards = [
        'card1' => 'Card 1',
        'card2' => 'Card 2',
        'card3' => 'Card 3',
        'card4' => 'Card 4',
    ];
@endphp

<div class="card-page">
    <div class="card-toolbar">
        <div>
            <h4>Choose Business Card</h4>
            <p>Select one design, then print the selected front and back card.</p>
        </div>

        <div class="card-actions">
            <a href="{{ route('profile.edit') }}" class="btn card-btn">Back Profile</a>
            <a href="{{ route('profile.business-card', ['design' => $selectedDesign, 'print' => 1]) }}" class="btn card-btn-dark">
                <i class="mdi mdi-printer-outline mr-1"></i> Print Selected
            </a>
        </div>
    </div>

    <div class="card-grid">
        @foreach($cards as $cardKey => $cardLabel)
            <div class="card-choice {{ $selectedDesign === $cardKey ? 'active' : '' }}">
                <div class="card-choice-head">
                    <strong>{{ $cardLabel }}</strong>
                    <a href="{{ route('profile.business-card', ['design' => $cardKey]) }}" class="card-select-link">
                        {{ $selectedDesign === $cardKey ? 'Selected' : 'Select' }}
                    </a>
                </div>

                <div class="mockup">
                    <div class="bc {{ $cardKey }} front">
                        <div class="bc-front">
                            <div class="left">
                                <div class="logo-row">
                                    <div class="logo-box">
                                        @if($logo)
                                            <img src="{{ $logo }}" alt="Logo">
                                        @else
                                            <i class="mdi mdi-medical-bag"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="brand">{{ $brandName }}<small>Pharmacy</small></div>
                                        <div class="tag">Health • Care • Trust</div>
                                    </div>
                                </div>
                            </div>

                            <div class="right">
                                <div class="person">{{ $displayName }}</div>
                                <div class="role">{{ $roleName }}</div>
                                <div class="line"></div>

                                <div class="info">
                                    <div><i class="mdi mdi-phone"></i> {{ $brandPhone }}</div>
                                    <div><i class="mdi mdi-email-outline"></i> {{ $brandEmail }}</div>
                                    <div><i class="mdi mdi-web"></i> {{ $website }}</div>
                                    <div><i class="mdi mdi-map-marker"></i> {{ $brandAddress }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bc {{ $cardKey }} back">
                        <div class="bc-back">
                            <div class="back-logo">
                                <div class="logo-box">
                                    @if($logo)
                                        <img src="{{ $logo }}" alt="Logo">
                                    @else
                                        <i class="mdi mdi-medical-bag"></i>
                                    @endif
                                </div>

                                <div class="brand">{{ $brandName }}<small>Pharmacy</small></div>
                            </div>

                            <div class="back-text">Reliable medicine. Trusted care.</div>

                            <div class="bottom-info">
                                <div><i class="mdi mdi-phone"></i> {{ $brandPhone }}</div>
                                <div><i class="mdi mdi-email-outline"></i> {{ $brandEmail }}</div>
                                <div><i class="mdi mdi-web"></i> {{ $website }}</div>
                                <div><i class="mdi mdi-map-marker"></i> {{ $brandAddress }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="print-only">
    <div class="print-sheet">
        <div class="bc {{ $selectedDesign }} front">
            <div class="bc-front">
                <div class="left">
                    <div class="logo-row">
                        <div class="logo-box">
                            @if($logo)
                                <img src="{{ $logo }}" alt="Logo">
                            @else
                                <i class="mdi mdi-medical-bag"></i>
                            @endif
                        </div>
                        <div>
                            <div class="brand">{{ $brandName }}<small>Pharmacy</small></div>
                            <div class="tag">Health • Care • Trust</div>
                        </div>
                    </div>
                </div>

                <div class="right">
                    <div class="person">{{ $displayName }}</div>
                    <div class="role">{{ $roleName }}</div>
                    <div class="line"></div>

                    <div class="info">
                        <div><i class="mdi mdi-phone"></i> {{ $brandPhone }}</div>
                        <div><i class="mdi mdi-email-outline"></i> {{ $brandEmail }}</div>
                        <div><i class="mdi mdi-web"></i> {{ $website }}</div>
                        <div><i class="mdi mdi-map-marker"></i> {{ $brandAddress }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bc {{ $selectedDesign }} back">
            <div class="bc-back">
                <div class="back-logo">
                    <div class="logo-box">
                        @if($logo)
                            <img src="{{ $logo }}" alt="Logo">
                        @else
                            <i class="mdi mdi-medical-bag"></i>
                        @endif
                    </div>

                    <div class="brand">{{ $brandName }}<small>Pharmacy</small></div>
                </div>

                <div class="back-text">Reliable medicine. Trusted care.</div>

                <div class="bottom-info">
                    <div><i class="mdi mdi-phone"></i> {{ $brandPhone }}</div>
                    <div><i class="mdi mdi-email-outline"></i> {{ $brandEmail }}</div>
                    <div><i class="mdi mdi-web"></i> {{ $website }}</div>
                    <div><i class="mdi mdi-map-marker"></i> {{ $brandAddress }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($autoPrint)
<script>
    window.addEventListener('load', function () {
        setTimeout(function () {
            window.print();
        }, 400);
    });
</script>
@endif
@endsection