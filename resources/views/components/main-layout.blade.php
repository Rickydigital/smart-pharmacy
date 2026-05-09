<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <title>{{ config('app.name', 'Smart Pharmacy') }} | @yield('title', 'Dashboard') </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta content="Corporate Clean Dashboard UI" name="description" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" href="{{ asset('app-assets/images/logo-sm.png') }}">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    {{-- Vendor / Existing Assets --}}
    <link href="{{ asset('app-assets/libs/bootstrap-tagsinput/bootstrap-tagsinput.css') }}" rel="stylesheet" />
    <link href="{{ asset('app-assets/libs/switchery/switchery.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('app-assets/libs/multiselect/multi-select.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('app-assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('app-assets/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('app-assets/libs/bootstrap-select/bootstrap-select.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('app-assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('app-assets/libs/custombox/custombox.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('app-assets/libs/rwd-table/rwd-table.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('app-assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" id="bootstrap-stylesheet" />
    <link href="{{ asset('app-assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('app-assets/css/app.min.css') }}" rel="stylesheet" type="text/css" id="app-stylesheet" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font/css/materialdesignicons.min.css" rel="stylesheet">

<style>
:root{
    --sidebar-w:286px;
    --sidebar-mini-w:88px;
    --topbar-h:76px;

    --primary:#155dfc;
    --primary-2:#2563eb;
    --primary-dark:#0f3fbf;
    --primary-soft:#eff6ff;
    --primary-soft-2:#dbeafe;

    --body-bg:#f5f7fb;
    --surface:#ffffff;
    --surface-soft:#f8fbff;

    --text:#0f172a;
    --text-2:#334155;
    --muted:#64748b;
    --muted-2:#94a3b8;

    --line:#e2e8f0;
    --line-soft:#eef2f7;

    --success:#16a34a;
    --danger:#ef4444;
    --warning:#f59e0b;

    --radius:18px;
    --shadow-card:0 12px 28px rgba(15,23,42,.055);
    --shadow-soft:0 18px 45px rgba(15,23,42,.06);
}

*,
*::before,
*::after{
    box-sizing:border-box;
}

html,
body{
    min-height:100%;
    margin:0;
    padding:0;
}

body{
    font-family:'Inter',sans-serif;
    background:
        radial-gradient(circle at 88% 0%,rgba(37,99,235,.08),transparent 26%),
        linear-gradient(180deg,#f8fbff 0%,#f5f7fb 100%);
    color:var(--text-2);
    overflow-x:hidden;
}

a{
    text-decoration:none !important;
}

#wrapper{
    min-height:100vh;
}

/* =========================
   SIDEBAR
========================= */
.left-side-menu{
    position:fixed !important;
    top:0 !important;
    left:0 !important;
    bottom:0 !important;
    width:var(--sidebar-w) !important;
    height:100vh !important;
    background:#fff !important;
    border-right:1px solid rgba(226,232,240,.95) !important;
    box-shadow:8px 0 26px rgba(15,23,42,.045) !important;
    z-index:1040 !important;
    display:flex;
    flex-direction:column;
    overflow:hidden;
    transition:width .22s ease,transform .22s ease;
}

.left-side-menu::before{
    content:"";
    position:absolute;
    top:0;
    left:0;
    bottom:0;
    width:7px;
    background:linear-gradient(180deg,#0f4cff 0%,#2563eb 48%,#93c5fd 100%);
    z-index:4;
}

.sidebar-brand{
    position:relative;
    z-index:5;
    min-height:var(--topbar-h);
    height:var(--topbar-h);
    display:flex;
    align-items:center;
    justify-content:center;
    padding:0 18px 0 24px;
    border-bottom:1px solid var(--line-soft);
    background:#fff;
}

.sidebar-brand-link{
    width:100%;
    height:100%;
    display:flex !important;
    align-items:center !important;
    justify-content:center !important;
    overflow:hidden;
}

.brand-logo-full{
    display:block;
    width:210px;
    max-width:100%;
    max-height:58px;
    height:auto;
    object-fit:contain;
    object-position:center;
}

.brand-logo-sm{
    display:none;
    width:42px;
    height:42px;
    object-fit:contain;
    object-position:center;
}

.sidebar-mobile-close{
    position:absolute;
    right:14px;
    top:50%;
    transform:translateY(-50%);
    width:36px;
    height:36px;
    border:1px solid #e2e8f0;
    border-radius:12px;
    background:#fff;
    color:#334155;
    display:flex;
    align-items:center;
    justify-content:center;
    z-index:8;
    padding:0;
}

.sidebar-mobile-close i{
    font-size:20px;
}

.sidebar-mobile-close:hover{
    background:#eff6ff;
    border-color:#dbeafe;
    color:var(--primary);
}

.quick-search-wrap {
    position: relative;
}

.quick-search-panel {
    position: absolute;
    top: calc(100% + 12px);
    left: 0;
    width: 440px;
    max-height: 430px;
    overflow-y: auto;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 18px;
    box-shadow: 0 24px 70px rgba(15, 23, 42, .18);
    padding: 10px;
    display: none;
    z-index: 1055;
}

.quick-search-panel.is-open {
    display: block;
}

.quick-search-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 10px;
    border-radius: 14px;
    text-decoration: none;
    color: inherit;
    transition: .16s ease;
}

.quick-search-item:hover {
    background: #f8fafc;
    text-decoration: none;
}

.quick-search-icon {
    width: 36px;
    height: 36px;
    border-radius: 13px;
    background: #eff6ff;
    color: #2563eb;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    font-size: 18px;
}

.quick-search-title {
    font-weight: 950;
    color: #0f172a;
    font-size: 13px;
    line-height: 1.25;
}

.quick-search-subtitle {
    font-size: 12px;
    font-weight: 700;
    color: #64748b;
    margin-top: 2px;
}

.quick-search-type {
    display: inline-flex;
    margin-top: 5px;
    padding: 3px 8px;
    border-radius: 999px;
    background: #f1f5f9;
    color: #475569;
    font-size: 10px;
    font-weight: 950;
    text-transform: uppercase;
    letter-spacing: .04em;
}

.quick-search-empty {
    padding: 18px 12px;
    text-align: center;
    color: #64748b;
    font-size: 12px;
    font-weight: 750;
}

.mobile-search-panel {
    position: relative;
}

.mobile-quick-search-panel {
    position: relative;
    top: auto;
    left: auto;
    width: calc(100% - 24px);
    margin: 10px 12px 12px;
    max-height: 360px;
}

@media (max-width: 767.98px) {
    .quick-search-panel {
        width: 340px;
        right: 0;
        left: auto;
    }
}
/* =========================
   SIDEBAR SCROLL
========================= */
.slimscroll-menu{
    position:relative;
    z-index:5;
    flex:1 1 auto;
    min-height:0;
    overflow-y:auto !important;
    overflow-x:hidden !important;
    padding:14px 0 24px;
    scrollbar-width:thin;
    scrollbar-color:#cbd5e1 transparent;
}

.slimscroll-menu::-webkit-scrollbar{
    width:5px;
}

.slimscroll-menu::-webkit-scrollbar-track{
    background:transparent;
}

.slimscroll-menu::-webkit-scrollbar-thumb{
    background:#cbd5e1;
    border-radius:999px;
}

.slimscroll-menu::-webkit-scrollbar-thumb:hover{
    background:#94a3b8;
}

#sidebar-menu,
#side-menu{
    height:auto !important;
    overflow:visible !important;
}

#sidebar-menu ul{
    list-style:none;
    margin:0;
    padding:0;
}

#side-menu,
#sidebar-menu .metismenu{
    padding-bottom:10px;
}

#sidebar-menu .menu-title{
    padding:20px 22px 10px 28px;
    font-size:10px;
    font-weight:900;
    letter-spacing:.16em;
    text-transform:uppercase;
    color:#94a3b8;
}

#sidebar-menu > ul > li{
    position:relative;
    margin:0 14px 7px 18px;
}

#sidebar-menu ul li > a{
    display:flex !important;
    align-items:center !important;
    gap:12px !important;
    min-height:48px;
    padding:12px 14px !important;
    border-radius:14px !important;
    color:#475569 !important;
    font-size:13.5px !important;
    font-weight:750 !important;
    position:relative;
    overflow:hidden;
    transition:background .16s ease,color .16s ease,border-color .16s ease,box-shadow .16s ease !important;
}

#sidebar-menu ul li > a i{
    width:22px !important;
    height:22px !important;
    display:inline-flex !important;
    align-items:center !important;
    justify-content:center !important;
    font-size:19px !important;
    flex-shrink:0;
    color:#64748b !important;
    transition:color .16s ease;
}

#sidebar-menu ul li > a span{
    min-width:0;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}

#sidebar-menu ul li > a:hover{
    background:#f8fafc !important;
    color:var(--primary) !important;
    transform:none !important;
    box-shadow:none !important;
}

#sidebar-menu ul li > a:hover i{
    color:var(--primary) !important;
}

#sidebar-menu > ul > li.active > a,
#sidebar-menu > ul > li.mm-active > a,
#sidebar-menu ul li > a.active-link{
    color:var(--primary-dark) !important;
    background:linear-gradient(135deg,#eff6ff 0%,#ffffff 100%) !important;
    border:1px solid #dbeafe !important;
    box-shadow:0 10px 24px rgba(37,99,235,.09) !important;
}

#sidebar-menu > ul > li.active > a i,
#sidebar-menu > ul > li.mm-active > a i,
#sidebar-menu ul li > a.active-link i{
    color:var(--primary) !important;
}

#sidebar-menu > ul > li.active > a::before,
#sidebar-menu > ul > li.mm-active > a::before,
#sidebar-menu ul li > a.active-link::before{
    content:"";
    position:absolute;
    left:-18px;
    top:8px;
    bottom:8px;
    width:4px;
    border-radius:999px;
    background:#155dfc;
}

.menu-arrow{
    margin-left:auto !important;
    color:#94a3b8 !important;
    transition:transform .16s ease,color .16s ease;
}

li.mm-active > a .menu-arrow,
li.active > a .menu-arrow{
    transform:rotate(90deg);
    color:var(--primary) !important;
}

.sidebar-mini-badge{
    margin-left:auto;
    padding:4px 8px;
    border-radius:999px;
    background:#eff6ff;
    color:#2563eb;
    font-size:10px;
    font-weight:900;
}

/* =========================
   SUBMENU
========================= */
#side-menu .nav-second-level{
    position:static !important;
    width:100% !important;
    margin:8px 0 0 0 !important;
    padding:8px !important;
    border-radius:16px !important;
    background:#f8fafc !important;
    border:1px solid #edf2f7;
    display:none !important;
    overflow:hidden !important;
}

#side-menu > li.mm-active > .nav-second-level,
#side-menu > li.active > .nav-second-level,
#side-menu .nav-second-level.mm-show,
#side-menu .nav-second-level[aria-expanded="true"]{
    display:block !important;
}

#side-menu .nav-second-level li{
    margin-bottom:4px;
}

#side-menu .nav-second-level li:last-child{
    margin-bottom:0;
}

#side-menu .nav-second-level li a{
    display:flex !important;
    align-items:center !important;
    min-height:39px;
    padding:9px 12px 9px 36px !important;
    border-radius:12px !important;
    font-size:12.5px !important;
    font-weight:700 !important;
    color:#64748b !important;
    white-space:normal !important;
    line-height:1.35 !important;
    position:relative;
}

#side-menu .nav-second-level li a::before{
    content:"";
    position:absolute;
    left:18px;
    top:50%;
    width:6px;
    height:6px;
    border-radius:50%;
    background:#cbd5e1;
    transform:translateY(-50%);
}

#side-menu .nav-second-level li.active > a,
#side-menu .nav-second-level li a:hover{
    background:#fff !important;
    color:var(--primary) !important;
}

#side-menu .nav-second-level li.active > a::before,
#side-menu .nav-second-level li a:hover::before{
    background:var(--primary);
}

/* =========================
   SIDEBAR FOOTER CARD
========================= */
.sidebar-footer-card{
    position:relative;
    z-index:5;
    margin:14px 16px 4px 22px;
    padding:16px;
    border-radius:18px;
    background:linear-gradient(180deg,#eff6ff,#ffffff);
    border:1px solid #dbeafe;
}

.sidebar-footer-icon{
    width:42px;
    height:42px;
    border-radius:14px;
    background:linear-gradient(135deg,#2563eb,#1d4ed8);
    color:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:20px;
    margin-bottom:10px;
    box-shadow:0 12px 24px rgba(37,99,235,.22);
}

.sidebar-footer-card h6{
    font-size:14px;
    font-weight:900;
    color:#0f172a;
    margin:0 0 4px;
}

.sidebar-footer-card p{
    font-size:12px;
    font-weight:600;
    color:#64748b;
    line-height:1.5;
    margin:0 0 12px;
}

.sidebar-footer-card .btn{
    border-radius:12px;
    font-weight:800;
    font-size:12px;
    min-height:38px;
}

/* =========================
   TOPBAR
========================= */
.navbar-custom{
    position:fixed !important;
    top:0 !important;
    left:var(--sidebar-w) !important;
    right:0 !important;
    min-height:var(--topbar-h) !important;
    height:var(--topbar-h) !important;
    background:rgba(255,255,255,.94) !important;
    backdrop-filter:blur(16px);
    border-bottom:1px solid rgba(226,232,240,.95) !important;
    box-shadow:0 8px 24px rgba(15,23,42,.045) !important;
    z-index:1030 !important;
    display:flex !important;
    align-items:center !important;
    justify-content:space-between !important;
    gap:14px;
    padding:10px 20px !important;
    transition:left .22s ease;
}

.topbar-left-area,
.topbar-right-area{
    display:flex;
    align-items:center;
    min-width:0;
}

.topbar-left-area{
    flex:1 1 auto;
    gap:10px;
}

.topbar-right-area{
    flex:0 0 auto;
    gap:9px;
}

.topbar-page-info{
    min-width:0;
    display:flex;
    flex-direction:column;
    line-height:1.15;
}

.topbar-page-kicker{
    display:inline-block;
    font-size:10px;
    font-weight:900;
    letter-spacing:.14em;
    text-transform:uppercase;
    color:#94a3b8;
    margin-bottom:2px;
}

.topbar-page-info h5{
    margin:0;
    font-size:17px;
    font-weight:900;
    color:#0f172a;
    letter-spacing:-.02em;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}

.topbar-page-info small{
    font-size:12px;
    color:#64748b;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}

.topbar-search{
    position:relative;
    width:min(440px,32vw);
    margin-left:12px;
}

.topbar-search input{
    width:100%;
    height:44px;
    padding:0 50px 0 42px;
    border-radius:14px;
    border:1px solid #e2e8f0;
    background:#fff;
    color:#334155;
    font-size:13px;
    font-weight:650;
    outline:none;
}

.topbar-search input:focus{
    border-color:#bfdbfe;
    box-shadow:0 0 0 4px rgba(37,99,235,.08);
}

.topbar-search .search-icon,
.topbar-search .mdi-magnify{
    position:absolute;
    left:14px;
    top:50%;
    transform:translateY(-50%);
    color:#94a3b8;
    font-size:19px;
}

.search-shortcut{
    position:absolute;
    right:10px;
    top:50%;
    transform:translateY(-50%);
    padding:4px 7px;
    border-radius:8px;
    background:#f8fafc;
    border:1px solid #e2e8f0;
    font-size:10px;
    font-weight:900;
    color:#64748b;
}

.button-menu-mobile,
.topbar-icon-btn{
    width:42px;
    height:42px;
    min-width:42px;
    border-radius:14px;
    border:1px solid #e2e8f0;
    background:#fff;
    color:#334155 !important;
    display:inline-flex !important;
    align-items:center !important;
    justify-content:center !important;
    transition:background .16s ease,color .16s ease,border-color .16s ease;
    position:relative;
    padding:0;
}

.button-menu-mobile i,
.topbar-icon-btn i{
    font-size:20px;
}

.button-menu-mobile:hover,
.topbar-icon-btn:hover{
    background:#eff6ff;
    color:var(--primary) !important;
    border-color:#dbeafe;
}

.topbar-divider{
    width:1px;
    height:28px;
    background:#e5e7eb;
}

.topbar-badge,
.topbar-notification-dot{
    position:absolute;
    top:-5px;
    right:-5px;
    min-width:18px;
    height:18px;
    padding:0 5px;
    border-radius:999px;
    background:#ef4444;
    color:#fff;
    font-size:10px;
    font-weight:900;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    border:2px solid #fff;
}

.topbar-badge.is-blue{
    background:#2563eb;
}

/* =========================
   USER / PROFILE
========================= */
.nav-user,
.premium-user-trigger,
.topbar-user{
    display:flex !important;
    align-items:center !important;
    gap:10px;
    min-height:46px !important;
    padding:5px 10px 5px 6px !important;
    border-radius:16px;
    background:#fff;
    border:1px solid #e2e8f0;
    transition:background .16s ease,border-color .16s ease;
    cursor:pointer;
}

.nav-user:hover,
.premium-user-trigger:hover,
.topbar-user:hover{
    background:#eff6ff;
    border-color:#dbeafe;
}

.avatar-initials,
.user-avatar{
    width:38px;
    height:38px;
    border-radius:14px;
    background:linear-gradient(135deg,#2563eb,#1d4ed8);
    color:#fff;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    font-weight:900;
    font-size:13px;
    flex-shrink:0;
    box-shadow:0 10px 20px rgba(37,99,235,.20);
}

.user-meta,
.user-info{
    display:flex;
    flex-direction:column;
    line-height:1.08;
    min-width:0;
}

.user-name{
    font-size:13px;
    font-weight:850;
    color:#0f172a;
    white-space:nowrap;
}

.user-role{
    font-size:11px;
    color:#64748b;
    white-space:nowrap;
    font-weight:750;
}

/* =========================
   DROPDOWNS
========================= */
.premium-dropdown,
.dropdown-menu{
    border:1px solid rgba(226,232,240,.95) !important;
    border-radius:18px !important;
    padding:10px !important;
    background:#fff !important;
    box-shadow:0 18px 42px rgba(15,23,42,.14) !important;
}

.topbar-dropdown-menu{
    position:absolute;
    top:calc(100% + 12px);
    right:0;
    min-width:260px;
    display:none;
    z-index:1080;
}

.topbar-dropdown.show .topbar-dropdown-menu{
    display:block;
}

.premium-profile-dropdown{
    min-width:250px;
}

.premium-profile-header{
    padding:12px 14px 14px;
    text-align:center;
}

.premium-profile-avatar{
    width:54px;
    height:54px;
    margin:0 auto 10px;
    border-radius:18px;
    background:linear-gradient(135deg,#2563eb,#1d4ed8);
    color:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:900;
    box-shadow:0 10px 20px rgba(37,99,235,.2);
}

.premium-dropdown-item,
.dropdown-item{
    display:flex !important;
    align-items:center;
    gap:8px;
    padding:10px 12px !important;
    border-radius:12px !important;
    font-size:13px;
    font-weight:750;
    color:#334155 !important;
}

.premium-dropdown-item:hover,
.dropdown-item:hover{
    background:#eff6ff !important;
    color:#0f172a !important;
}

.premium-dropdown-item.text-danger,
.dropdown-item.text-danger{
    color:#dc2626 !important;
}

.topbar-dropdown{
    position:relative;
}

.topbar-dropdown-menu{
    position:absolute;
    top:calc(100% + 12px);
    right:0;
    min-width:260px;
    display:none !important;
    opacity:0;
    visibility:hidden;
    pointer-events:none;
    transform:translateY(6px);
    z-index:1080;
}

.topbar-dropdown.show > .topbar-dropdown-menu{
    display:block !important;
    opacity:1;
    visibility:visible;
    pointer-events:auto;
    transform:translateY(0);
}

.topbar-dropdown .dropdown-toggle::after{
    display:none !important;
}

/* =========================
   MOBILE SEARCH PANEL
========================= */
.mobile-search-panel{
    display:none;
    position:fixed;
    top:var(--topbar-h);
    left:0;
    right:0;
    z-index:1042;
    padding:12px 14px;
    background:#fff;
    border-bottom:1px solid #e2e8f0;
    box-shadow:0 12px 24px rgba(15,23,42,.08);
}

body.mobile-search-open .mobile-search-panel{
    display:block;
}

.mobile-search-panel .mobile-search-box{
    position:relative;
}

.mobile-search-panel input{
    width:100%;
    height:46px;
    border-radius:14px;
    border:1px solid #dbeafe;
    background:#f8fafc;
    padding:0 44px;
    outline:none;
    font-weight:700;
}

.mobile-search-panel input:focus{
    border-color:#93c5fd;
    box-shadow:0 0 0 4px rgba(37,99,235,.08);
}

.mobile-search-panel i{
    position:absolute;
    left:14px;
    top:50%;
    transform:translateY(-50%);
    color:#2563eb;
    font-size:20px;
}

/* =========================
   CONTENT
========================= */
.content-page{
    min-height:calc(100vh - var(--topbar-h));
    display:flex;
    flex-direction:column;
    margin-left:var(--sidebar-w) !important;
    margin-top:var(--topbar-h) !important;
    transition:margin-left .22s ease;
}

.content,
.content-shell{
    flex:1 1 auto;
    padding:24px 28px;
}

/* =========================
   FOOTER
========================= */
.app-footer{
    flex-shrink:0;
    margin-top:auto;
    padding:0 28px 22px;
    background:transparent;
}

.app-footer-inner{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:14px;
    flex-wrap:wrap;
    padding:15px 18px;
    border:1px solid rgba(226,232,240,.95);
    border-radius:20px;
    background:rgba(255,255,255,.88);
    box-shadow:0 10px 24px rgba(15,23,42,.04);
}

.app-footer-copy{
    font-size:12px;
    font-weight:700;
    color:#64748b;
}

.app-footer-right{
    display:flex;
    align-items:center;
    gap:8px;
    flex-wrap:wrap;
}

.app-footer-badge{
    display:inline-flex;
    align-items:center;
    gap:7px;
    padding:8px 12px;
    border-radius:999px;
    background:#eff6ff;
    color:#2563eb;
    font-size:12px;
    font-weight:800;
}

/* =========================
   COMMON UI
========================= */
.alert{
    border:none;
    border-radius:16px;
    font-size:.84rem;
    font-weight:600;
}

.alert-success{
    background:#f0fdf4;
    color:#15803d;
    border-left:4px solid #22c55e;
}

.alert-danger{
    background:#fef2f2;
    color:#b91c1c;
    border-left:4px solid #f87171;
}

.card,
.card-box{
    border-radius:18px !important;
    box-shadow:0 1px 4px rgba(0,0,0,.06) !important;
    border:1px solid #e2e8f0 !important;
}

.btn-primary{
    background:#2563eb !important;
    border-color:#2563eb !important;
}

.btn-primary:hover{
    background:#1d4ed8 !important;
    border-color:#1d4ed8 !important;
}

/* =========================
   CHAT FLOAT
========================= */
.chat-fab{
    position:fixed;
    right:28px;
    bottom:28px;
    width:54px;
    height:54px;
    border-radius:50%;
    background:linear-gradient(135deg,#2563eb,#1d4ed8);
    color:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:1.2rem;
    box-shadow:0 16px 35px rgba(37,99,235,.32);
    z-index:1050;
    text-decoration:none;
    transition:transform .2s,box-shadow .2s;
}

.chat-fab:hover{
    color:#fff;
    transform:translateY(-3px);
    box-shadow:0 20px 42px rgba(37,99,235,.42);
}

/* =========================
   COLLAPSED DESKTOP
========================= */
body.sidebar-collapsed .left-side-menu{
    width:var(--sidebar-mini-w) !important;
}

body.sidebar-collapsed .navbar-custom{
    left:var(--sidebar-mini-w) !important;
}

body.sidebar-collapsed .content-page{
    margin-left:var(--sidebar-mini-w) !important;
}

body.sidebar-collapsed .sidebar-brand{
    padding:0 12px !important;
}

body.sidebar-collapsed .brand-logo-full{
    display:none !important;
}

body.sidebar-collapsed .brand-logo-sm{
    display:block !important;
}

body.sidebar-collapsed .sidebar-footer-card,
body.sidebar-collapsed .sidebar-mini-badge{
    display:none !important;
}

body.sidebar-collapsed #sidebar-menu .menu-title,
body.sidebar-collapsed #sidebar-menu ul li > a span,
body.sidebar-collapsed #sidebar-menu ul li > a .menu-arrow{
    display:none !important;
}

body.sidebar-collapsed #sidebar-menu > ul > li{
    margin-left:14px !important;
    margin-right:14px !important;
}

body.sidebar-collapsed #sidebar-menu ul li > a{
    justify-content:center !important;
    min-height:50px !important;
    padding:12px !important;
    border-radius:16px !important;
}

body.sidebar-collapsed #sidebar-menu ul li > a i{
    margin:0 !important;
}

body.sidebar-collapsed #sidebar-menu ul li > a:hover{
    background:#eff6ff !important;
    transform:none !important;
}

body.sidebar-collapsed #side-menu .nav-second-level{
    display:none !important;
}

/* =========================
   MOBILE BACKDROP
========================= */
.sidebar-backdrop,
.sidebar-overlay{
    display:none;
    position:fixed;
    inset:0;
    background:rgba(15,23,42,.36);
    z-index:1045;
    cursor:pointer;
    backdrop-filter:blur(2px);
}

/* =========================
   RESPONSIVE
========================= */
@media (max-width:991px){
    .left-side-menu{
        transform:translateX(-100%) !important;
        width:278px !important;
        z-index:1050 !important;
        border-radius:0 22px 22px 0;
        box-shadow:none !important;
    }

    body.sidebar-open .left-side-menu{
        transform:translateX(0) !important;
        box-shadow:10px 0 28px rgba(15,23,42,.14) !important;
    }

    body.sidebar-open .sidebar-backdrop,
    body.sidebar-open .sidebar-overlay{
        display:block;
    }

    .navbar-custom{
        left:0 !important;
        padding:10px 12px !important;
    }

    .content-page{
        margin-left:0 !important;
    }

    .topbar-search{
        display:none !important;
    }

    .topbar-page-info small{
        display:none;
    }

    .topbar-logo-sm{
        display:flex !important;
    }

    .content,
    .content-shell{
        padding:18px 14px;
    }

    .app-footer{
        padding:0 14px 16px;
    }

    body.sidebar-collapsed .left-side-menu{
        width:278px !important;
    }

    body.sidebar-collapsed .navbar-custom{
        left:0 !important;
    }

    body.sidebar-collapsed .content-page{
        margin-left:0 !important;
    }

    body.sidebar-collapsed #sidebar-menu .menu-title,
    body.sidebar-collapsed #sidebar-menu ul li > a span,
    body.sidebar-collapsed #sidebar-menu ul li > a .menu-arrow{
        display:inline-flex !important;
    }

    body.sidebar-collapsed #sidebar-menu ul li > a{
        justify-content:flex-start !important;
    }
}

@media (min-width:992px){
    .mobile-search-btn{
        display:none !important;
    }
}

@media (max-width:1199px){
    .user-meta,
    .user-info{
        display:none !important;
    }
}

@media (max-width:767px){
    :root{
        --topbar-h:72px;
    }

    .topbar-page-kicker{
        display:none;
    }

    .topbar-page-info h5{
        font-size:16px;
    }

    .button-menu-mobile,
    .topbar-icon-btn{
        width:40px;
        height:40px;
        min-width:40px;
        border-radius:13px;
    }

    .navbar-custom{
        gap:8px;
    }

    .topbar-left-area{
        gap:8px;
    }

    .topbar-right-area{
        gap:7px;
    }

    .app-footer-inner{
        border-radius:16px;
        padding:12px 14px;
    }

    .app-footer-right{
        gap:6px;
    }

    .app-footer-badge{
        padding:7px 10px;
        font-size:11px;
    }
}

@media (max-width:420px){
    .topbar-logo-sm{
        display:none !important;
    }

    .topbar-page-info h5{
        max-width:130px;
    }

    .left-side-menu{
        width:268px !important;
    }

    body.sidebar-collapsed .left-side-menu{
        width:268px !important;
    }
}
</style>
    @stack('styles')
</head>

<body>
    <div id="wrapper">
        @include('components.side-bar')
        @include('components.top-bar')

        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <main class="content-page">
            <div class="content-shell">

                @php
                    $smartState = config('smartcontrol.enabled')
                        ? app(\App\Services\SmartControl\RuntimeGuard::class)->currentState()
                        : null;
                @endphp

                @if($smartState && $smartState->allowed && $smartState->subscription_status === 'grace')
                    <div class="alert alert-warning border-0 rounded-4 shadow-sm mb-3">
                        <strong>Subscription Grace Period:</strong>
                        {{ $smartState->message }}
                    </div>
                @endif
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible mb-4" role="alert">
                        <i class="mdi mdi-check-circle-outline me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close ms-2" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible mb-4" role="alert">
                        <i class="mdi mdi-alert-circle-outline me-2"></i>
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close ms-2" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </div>

            @include('components.footer')
        </main>
    </div>

    {{-- Vendor JS --}}
    <script src="{{ asset('app-assets/js/vendor.min.js') }}"></script>
    <script src="{{ asset('app-assets/bootstrap-5.0.2/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('app-assets/js/app.min.js') }}"></script>
    <script src="{{ asset('app-assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('app-assets/libs/jquery-steps/jquery.steps.min.js') }}"></script>
    <script src="{{ asset('app-assets/libs/jquery-validation/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('app-assets/libs/switchery/switchery.min.js') }}"></script>
    <script src="{{ asset('app-assets/libs/bootstrap-tagsinput/bootstrap-tagsinput.min.js') }}"></script>
    <script src="{{ asset('app-assets/libs/multiselect/jquery.multi-select.js') }}"></script>
    <script src="{{ asset('app-assets/libs/jquery-quicksearch/jquery.quicksearch.min.js') }}"></script>
    <script src="{{ asset('app-assets/libs/autocomplete/jquery.autocomplete.min.js') }}"></script>
    <script src="{{ asset('app-assets/libs/bootstrap-select/bootstrap-select.min.js') }}"></script>
    <script src="{{ asset('app-assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js') }}"></script>
    <script src="{{ asset('app-assets/libs/bootstrap-maxlength/bootstrap-maxlength.min.js') }}"></script>
    <script src="{{ asset('app-assets/libs/bootstrap-filestyle2/bootstrap-filestyle.min.js') }}"></script>
    <script src="{{ asset('app-assets/libs/custombox/custombox.min.js') }}"></script>
    <script src="{{ asset('app-assets/libs/rwd-table/rwd-table.min.js') }}"></script>
    <script src="{{ asset('app-assets/libs/raphael/raphael.min.js') }}"></script>
    <script src="{{ asset('app-assets/libs/morris-js/morris.min.js') }}"></script>
    <script src="{{ asset('app-assets/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('app-assets/js/pages/form-wizard.init.js') }}"></script>
    <script src="{{ asset('app-assets/js/pages/sweetalerts.init.js') }}"></script>
    <script src="{{ asset('app-assets/select2/js/select2Init.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const body = document.body;
    const desktopBreakpoint = 992;

    const sidebarToggle = document.querySelector('.button-menu-mobile');
    const sidebarOverlay =
        document.getElementById('sidebarBackdrop') ||
        document.getElementById('sidebarOverlay');

    const mobileSearchToggle = document.getElementById('mobileSearchToggle');
    const mobileSearchPanel = document.getElementById('mobileSearchPanel');

    const searchUrl = @json(route('quick-search'));
    const quickSearchInputs = document.querySelectorAll('.js-quick-search-input');

    let quickSearchTimer = null;

    function isMobile() {
        return window.innerWidth < desktopBreakpoint;
    }

    function closeSidebar() {
        body.classList.remove('sidebar-open');
    }

    function closeMobileSearch() {
        body.classList.remove('mobile-search-open');
        closeAllQuickSearchPanels();
    }

    function closeTopbarDropdowns(exceptDropdown = null) {
        document.querySelectorAll('.topbar-dropdown.show').forEach(function (dropdown) {
            if (exceptDropdown && dropdown === exceptDropdown) {
                return;
            }

            dropdown.classList.remove('show');
        });
    }

    function closeOtherSidebarMenus(currentParent) {
        document.querySelectorAll('#side-menu > li.mm-active').forEach(function (item) {
            if (item === currentParent) {
                return;
            }

            const submenu = item.querySelector(':scope > .nav-second-level');
            const trigger = item.querySelector(':scope > a.has-arrow');

            item.classList.remove('mm-active');

            if (!item.classList.contains('active-by-route')) {
                item.classList.remove('active');
            }

            if (submenu) {
                submenu.classList.remove('mm-show');
                submenu.setAttribute('aria-expanded', 'false');
            }

            if (trigger) {
                trigger.setAttribute('aria-expanded', 'false');
            }
        });
    }

    function resetResponsiveState() {
        if (isMobile()) {
            body.classList.remove('sidebar-collapsed');
        } else {
            body.classList.remove('sidebar-open');
            body.classList.remove('mobile-search-open');
        }

        closeTopbarDropdowns();
        closeAllQuickSearchPanels();
    }

    /*
     |--------------------------------------------------------------------------
     | Quick Search
     |--------------------------------------------------------------------------
     */
    function escapeHtml(value) {
        return String(value || '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function closeAllQuickSearchPanels() {
        document.querySelectorAll('.js-quick-search-panel').forEach(function (panel) {
            panel.classList.remove('is-open');
        });
    }

    function getQuickSearchPanel(input) {
        const wrap =
            input.closest('.quick-search-wrap') ||
            input.closest('.mobile-search-panel') ||
            document;

        return wrap.querySelector('.js-quick-search-panel');
    }

    function renderQuickSearchPanel(panel, results, query) {
        panel.classList.add('is-open');

        if (!query || query.length < 2) {
            panel.innerHTML = '<div class="quick-search-empty">Type at least 2 characters to search.</div>';
            return;
        }

        if (!results.length) {
            panel.innerHTML = '<div class="quick-search-empty">No result found.</div>';
            return;
        }

        panel.innerHTML = results.map(function (item) {
            return `
                <a href="${escapeHtml(item.url)}" class="quick-search-item">
                    <span class="quick-search-icon">
                        <i class="mdi ${escapeHtml(item.icon)}"></i>
                    </span>

                    <span>
                        <span class="quick-search-title">${escapeHtml(item.title)}</span>
                        <span class="quick-search-subtitle">${escapeHtml(item.subtitle || '')}</span>
                        <span class="quick-search-type">${escapeHtml(item.type)}</span>
                    </span>
                </a>
            `;
        }).join('');
    }

    function performQuickSearch(input) {
        const query = input.value.trim();
        const panel = getQuickSearchPanel(input);

        if (!panel) {
            return;
        }

        if (query.length < 2) {
            renderQuickSearchPanel(panel, [], query);
            return;
        }

        panel.classList.add('is-open');
        panel.innerHTML = '<div class="quick-search-empty">Searching...</div>';

        fetch(`${searchUrl}?q=${encodeURIComponent(query)}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
            },
        })
            .then(async function (response) {
                const data = await response.json();

                if (!response.ok || !data.ok) {
                    throw new Error(data.message || 'Unable to search.');
                }

                return data;
            })
            .then(function (data) {
                renderQuickSearchPanel(panel, data.results || [], query);
            })
            .catch(function () {
                panel.innerHTML = '<div class="quick-search-empty text-danger">Search failed.</div>';
            });
    }

    quickSearchInputs.forEach(function (input) {
        input.addEventListener('input', function () {
            clearTimeout(quickSearchTimer);

            quickSearchTimer = setTimeout(function () {
                performQuickSearch(input);
            }, 250);
        });

        input.addEventListener('focus', function () {
            const query = input.value.trim();
            const panel = getQuickSearchPanel(input);

            if (panel) {
                renderQuickSearchPanel(panel, [], query);
            }
        });

        input.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeAllQuickSearchPanels();
            }
        });
    });

    document.addEventListener('keydown', function (event) {
        const isShortcut = (event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k';

        if (!isShortcut) {
            return;
        }

        event.preventDefault();

        const desktopInput = document.querySelector('.topbar-search .js-quick-search-input');

        if (desktopInput) {
            closeSidebar();
            closeMobileSearch();
            closeTopbarDropdowns();

            desktopInput.focus();
        }
    });

    /*
     |--------------------------------------------------------------------------
     | Mark route-active sidebar parents
     |--------------------------------------------------------------------------
     */
    document.querySelectorAll('#side-menu > li.active, #side-menu > li.mm-active').forEach(function (item) {
        item.classList.add('active-by-route');
    });

    /*
     |--------------------------------------------------------------------------
     | Sidebar toggle
     |--------------------------------------------------------------------------
     */
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();

            closeMobileSearch();
            closeTopbarDropdowns();
            closeAllQuickSearchPanels();

            if (isMobile()) {
                body.classList.toggle('sidebar-open');
            } else {
                body.classList.toggle('sidebar-collapsed');
            }
        });
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function (event) {
            event.preventDefault();

            closeSidebar();
            closeMobileSearch();
            closeTopbarDropdowns();
            closeAllQuickSearchPanels();
        });
    }

    /*
     |--------------------------------------------------------------------------
     | Mobile search
     |--------------------------------------------------------------------------
     */
    if (mobileSearchToggle && mobileSearchPanel) {
        mobileSearchToggle.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();

            closeSidebar();
            closeTopbarDropdowns();
            closeAllQuickSearchPanels();

            body.classList.toggle('mobile-search-open');

            if (body.classList.contains('mobile-search-open')) {
                setTimeout(function () {
                    const input = mobileSearchPanel.querySelector('input');

                    if (input) {
                        input.focus();
                    }
                }, 80);
            }
        });

        mobileSearchPanel.addEventListener('click', function (event) {
            event.stopPropagation();
        });
    }

    /*
     |--------------------------------------------------------------------------
     | Topbar dropdowns
     |--------------------------------------------------------------------------
     */
    document.querySelectorAll('.js-topbar-dropdown').forEach(function (trigger) {
        trigger.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();

            const dropdown = trigger.closest('.topbar-dropdown');

            if (!dropdown) {
                return;
            }

            const willOpen = !dropdown.classList.contains('show');

            closeMobileSearch();
            closeTopbarDropdowns(dropdown);
            closeAllQuickSearchPanels();

            dropdown.classList.toggle('show', willOpen);
        }, true);
    });

    document.querySelectorAll('.topbar-dropdown-menu').forEach(function (menu) {
        menu.addEventListener('click', function (event) {
            event.stopPropagation();
        });
    });

    /*
     |--------------------------------------------------------------------------
     | Sidebar dropdowns - fixed
     |--------------------------------------------------------------------------
     */
    document.querySelectorAll('#side-menu > li > a.has-arrow').forEach(function (link) {
        link.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();

            if (body.classList.contains('sidebar-collapsed') && !isMobile()) {
                return false;
            }

            const parent = link.closest('li');

            if (!parent) {
                return false;
            }

            const submenu = parent.querySelector(':scope > .nav-second-level');

            if (!submenu) {
                return false;
            }

            const willOpen = !submenu.classList.contains('mm-show');

            closeOtherSidebarMenus(parent);
            closeAllQuickSearchPanels();

            parent.classList.toggle('mm-active', willOpen);
            parent.classList.toggle('active', willOpen || parent.classList.contains('active-by-route'));

            submenu.classList.toggle('mm-show', willOpen);

            link.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            submenu.setAttribute('aria-expanded', willOpen ? 'true' : 'false');

            return false;
        }, true);
    });

    /*
     |--------------------------------------------------------------------------
     | Close global overlays
     |--------------------------------------------------------------------------
     */
    document.addEventListener('click', function (event) {
        if (
            event.target.closest('.quick-search-wrap') ||
            event.target.closest('.mobile-search-panel') ||
            event.target.closest('.topbar-dropdown')
        ) {
            return;
        }

        closeTopbarDropdowns();
        closeMobileSearch();
        closeAllQuickSearchPanels();
    });

    document.addEventListener('keyup', function (event) {
        if (event.key === 'Escape') {
            closeSidebar();
            closeMobileSearch();
            closeTopbarDropdowns();
            closeAllQuickSearchPanels();
        }
    });

    window.addEventListener('resize', function () {
        resetResponsiveState();
    });

    resetResponsiveState();
});
</script>

    @stack('scripts')
</body>
</html>