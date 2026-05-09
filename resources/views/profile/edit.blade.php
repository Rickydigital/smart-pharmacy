@extends('components.main-layout')

@section('title', 'My Profile')
@section('page-title', 'My Profile')
@section('page-kicker', 'Account')
@section('page-subtitle', 'Update your personal information.')

@section('content')
<style>
.profile-wrap{max-width:980px;margin:0 auto}
.profile-head,.profile-card{background:#fff;border:1px solid #e5edf7;border-radius:24px;box-shadow:0 18px 45px rgba(15,23,42,.06)}
.profile-head{padding:22px;display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:18px}
.profile-user{display:flex;align-items:center;gap:14px}
.profile-avatar{width:64px;height:64px;border-radius:20px;background:linear-gradient(135deg,#2563eb,#0ea5e9);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:950;font-size:24px}
.profile-name{margin:0;color:#0f172a;font-weight:950;letter-spacing:-.03em}
.profile-meta{margin:4px 0 0;color:#64748b;font-size:13px;font-weight:700}
.profile-actions{display:flex;gap:10px;flex-wrap:wrap}
.btn-soft,.btn-dark-soft{height:42px;border-radius:14px;font-weight:900;padding:0 15px}
.btn-soft{border:1px solid #dbeafe;background:#fff;color:#2563eb}
.btn-dark-soft{border:0;background:#0f172a;color:#fff}
.profile-card{overflow:hidden}
.profile-card-title{padding:18px 22px;border-bottom:1px solid #eef2f7;background:#f8fbff}
.profile-card-title h5{margin:0;font-weight:950;color:#0f172a}
.profile-card-title small{color:#64748b;font-weight:700}
.profile-body{padding:22px}
.form-labelx{font-size:13px;color:#334155;font-weight:900;margin-bottom:7px}
.form-controlx{height:48px;border-radius:15px;border:1px solid #dbeafe;font-weight:750;color:#0f172a}
.form-controlx:focus{border-color:#2563eb;box-shadow:0 0 0 4px rgba(37,99,235,.10)}
.form-controlx[readonly]{background:#f8fafc;color:#64748b}
.hint{font-size:12px;color:#64748b;font-weight:650;margin-top:6px}
.err{font-size:12px;color:#dc2626;font-weight:800;margin-top:6px}
.success-box{border-radius:16px;background:#ecfdf5;border:1px solid #bbf7d0;color:#047857;padding:12px 14px;font-weight:850;margin-bottom:16px}
.save-row{display:flex;justify-content:flex-end;gap:10px;margin-top:8px}
.save-btn,.cancel-btn{height:46px;border-radius:15px;font-weight:950;padding:0 20px}
.cancel-btn{border:1px solid #dbeafe;background:#fff;color:#334155}
.card-modal{position:fixed;inset:0;background:rgba(15,23,42,.58);z-index:9999;display:none;align-items:center;justify-content:center;padding:18px}
.card-modal.show{display:flex}
.card-box{width:min(960px,100%);max-height:92vh;overflow:auto;background:#fff;border-radius:26px;padding:20px;box-shadow:0 30px 80px rgba(0,0,0,.25)}
.card-box-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px}
.card-box-head h5{margin:0;font-weight:950;color:#0f172a}
.card-close{border:0;background:#f1f5f9;width:38px;height:38px;border-radius:14px;font-size:22px}
.design-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:14px}
.design-option{border:2px solid #e5edf7;border-radius:22px;padding:14px;background:#f8fafc;cursor:pointer}
.design-option.active{border-color:#2563eb;background:#eff6ff}
.biz-card{width:100%;aspect-ratio:1.75/1;border-radius:20px;overflow:hidden;position:relative;padding:22px;display:flex;flex-direction:column;justify-content:space-between;box-shadow:0 16px 35px rgba(15,23,42,.12)}
.bc-logo{display:flex;align-items:center;gap:10px}
.bc-mark{width:44px;height:44px;border-radius:14px;background:#fff;display:flex;align-items:center;justify-content:center;font-size:26px}
.bc-brand{font-weight:950;font-size:22px;line-height:1;color:#0f172a}
.bc-small{font-size:12px;font-weight:750;opacity:.82}
.bc-name{font-size:19px;font-weight:950;line-height:1.1}
.bc-role{font-size:12px;font-weight:750;margin-top:4px}
.bc-info{font-size:11px;font-weight:750;line-height:1.8}
.d1{background:linear-gradient(135deg,#fff 0%,#f8fbff 58%,#dbeafe 100%);color:#0f172a}
.d1:after{content:"";position:absolute;right:-38px;top:0;width:150px;height:100%;background:linear-gradient(135deg,#2563eb,#0ea5e9);border-radius:80px 0 0 80px}
.d2{background:linear-gradient(135deg,#fff 0%,#fff 42%,#0f172a 43%,#1e3a8a 100%);color:#fff}
.d2 .bc-brand,.d2 .bc-logo .bc-small{color:#0f172a}
.d2 .bc-name{color:#f8d37a}
.d3{background:linear-gradient(135deg,#fff,#f8fafc);color:#0f172a;border-bottom:10px solid #2f8f6b}
.d3:after{content:"";position:absolute;right:-40px;top:-30px;width:150px;height:150px;background:#2f8f6b;border-radius:999px;opacity:.9}
.d4{background:linear-gradient(135deg,#fff 0%,#f8fbff 70%,#38bdf8 100%);color:#0f172a}
.d4:after{content:"";position:absolute;right:-18px;bottom:-45px;width:180px;height:120px;background:linear-gradient(135deg,#2563eb,#0ea5e9);border-radius:999px}
.bc-front{z-index:2;position:relative}
.card-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:16px}
.print-area{display:none}
@media(max-width:575.98px){
.profile-head{align-items:flex-start;flex-direction:column;border-radius:20px;padding:18px}
.profile-actions,.profile-actions button,.profile-actions a{width:100%}
.profile-actions button,.profile-actions a{display:flex;align-items:center;justify-content:center}
.profile-body{padding:18px}
.save-row,.card-actions{flex-direction:column-reverse}
.save-row .btn,.card-actions .btn{width:100%}
.design-grid{grid-template-columns:1fr}
}
@media print{
body *{visibility:hidden!important}
.print-area,.print-area *{visibility:visible!important}
.print-area{display:block!important;position:absolute;left:0;top:0;width:100%;height:100%;background:#fff;padding-top:32mm}
.print-card-box{width:88mm;height:54mm;margin:0 auto;border-radius:5mm;overflow:hidden}
.print-card-box .biz-card{width:88mm;height:54mm;border-radius:5mm;box-shadow:none;padding:7mm;box-sizing:border-box}
@page{size:A4 portrait;margin:0}
}
</style>

@php
    $displayName = $user->full_name ?: ($user->username ?: $user->email);
    $parts = preg_split('/\s+/', trim($displayName));
    $initials = strtoupper(substr($parts[0] ?? 'U', 0, 1));
    if (count($parts) > 1) $initials .= strtoupper(substr(end($parts), 0, 1));
    $roleName = $user->roles?->first()?->name ?? 'User';
    $pharmacy = $user->pharmacy;
    $brandName = $pharmacy?->name ?: config('app.name');
    $brandPhone = $pharmacy?->phone ?: ($user->phone ?: 'No phone');
    $brandEmail = $pharmacy?->email ?: $user->email;
    $brandAddress = $pharmacy?->address ?: 'Dar es Salaam, Tanzania';
    $logo = $pharmacy?->logo_path ? asset('storage/' . ltrim($pharmacy->logo_path, '/')) : null;
@endphp

<div class="profile-wrap">
    <div class="profile-head">
        <div class="profile-user">
            <div class="profile-avatar">{{ $initials }}</div>
            <div>
                <h3 class="profile-name">{{ $displayName }}</h3>
                <p class="profile-meta">{{ $roleName }} · {{ ucfirst($user->status ?? 'active') }}</p>
            </div>
        </div>

        <div class="profile-actions">
            <a href="{{ route('business-card.print') }}" target="_blank" class="btn btn-dark-soft">
                <i class="mdi mdi-card-account-details-outline mr-1"></i> Business Card
            </a>
            <a href="{{ Route::has('dashboard') ? route('dashboard') : url('/') }}" class="btn btn-soft">Dashboard</a>
        </div>
    </div>

    @if(session('success'))
        <div class="success-box"><i class="mdi mdi-check-circle-outline mr-1"></i>{{ session('success') }}</div>
    @endif

    <div class="profile-card">
        <div class="profile-card-title">
            <h5>Profile Details</h5>
            <small>Username and email are locked for account security.</small>
        </div>

        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PATCH')

            <div class="profile-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-labelx">First Name</label>
                        <input type="text" name="first_name" class="form-control form-controlx" value="{{ old('first_name', $user->first_name) }}" required>
                        @error('first_name') <div class="err">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-labelx">Last Name</label>
                        <input type="text" name="last_name" class="form-control form-controlx" value="{{ old('last_name', $user->last_name) }}">
                        @error('last_name') <div class="err">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-labelx">Username</label>
                        <input type="text" class="form-control form-controlx" value="{{ $user->username }}" readonly>
                        <div class="hint">Cannot be changed here.</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-labelx">Email</label>
                        <input type="email" class="form-control form-controlx" value="{{ $user->email }}" readonly>
                        <div class="hint">Cannot be changed here.</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-labelx">Phone</label>
                        <input type="text" name="phone" class="form-control form-controlx" value="{{ old('phone', $user->phone) }}" placeholder="Example: 0624592725">
                        @error('phone') <div class="err">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="save-row">
                    <a href="{{ Route::has('dashboard') ? route('dashboard') : url('/') }}" class="btn cancel-btn">Cancel</a>
                    <button type="submit" class="btn btn-primary save-btn">
                        <i class="mdi mdi-content-save-outline mr-1"></i> Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card-modal" id="cardModal">
    <div class="card-box">
        <div class="card-box-head">
            <h5>Choose Business Card Design</h5>
            <button type="button" class="card-close" onclick="closeCardModal()">×</button>
        </div>

        <div class="design-grid">
            @foreach(['d1','d2','d3','d4'] as $design)
                <div class="design-option {{ $loop->first ? 'active' : '' }}" data-design="{{ $design }}" onclick="selectCardDesign('{{ $design }}', this)">
                    @include('profile.partials.business-card', ['design' => $design])
                </div>
            @endforeach
        </div>

        <div class="card-actions">
            <button type="button" class="btn btn-soft" onclick="closeCardModal()">Cancel</button>
            <button type="button" class="btn btn-dark-soft" onclick="printSelectedCard()">Print Selected</button>
        </div>
    </div>
</div>

<div class="print-area">
    <div class="print-card-box" id="printCardBox">
        @include('profile.partials.business-card', ['design' => 'd1'])
    </div>
</div>

<script>
let selectedDesign = 'd1';

function openCardModal(){document.getElementById('cardModal').classList.add('show')}
function closeCardModal(){document.getElementById('cardModal').classList.remove('show')}

function selectCardDesign(design, el){
    selectedDesign = design;
    document.querySelectorAll('.design-option').forEach(item => item.classList.remove('active'));
    el.classList.add('active');
}

function printSelectedCard(){
    let card = document.querySelector('.design-option[data-design="'+selectedDesign+'"] .biz-card').cloneNode(true);
    let box = document.getElementById('printCardBox');
    box.innerHTML = '';
    box.appendChild(card);
    closeCardModal();
    setTimeout(() => window.print(), 200);
}
</script>
@endsection