@extends('components.main-layout')

@section('title', 'Product Setup')
@section('page-title', 'Product Setup')
@section('page-subtitle', 'Manage product types, categories, units, package structure and prices')

@section('content')
    @include('product-setup.partials._styles')

    @php
        $activeTab = $activeTab ?? request('tab', 'products');

        $tabRoutes = [
            'products' => route('product-setup.index', ['tab' => 'products']),
            'types' => route('product-setup.index', ['tab' => 'types']),
            'categories' => route('product-setup.index', ['tab' => 'categories']),
            'units' => route('product-setup.index', ['tab' => 'units']),
            'structure' => route('product-setup.index', ['tab' => 'structure']),
            'prices' => route('product-setup.index', ['tab' => 'prices']),
        ];
    @endphp

    <div class="container-fluid ps-page">
        @include('product-setup.partials._header')

        @if (session('success'))
            <div class="alert alert-success ps-alert">
                <i class="mdi mdi-check-circle-outline mr-1"></i>
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger ps-alert">
                <i class="mdi mdi-alert-circle-outline mr-1"></i>
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger ps-alert">
                <i class="mdi mdi-alert-circle-outline mr-1"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <div class="card ps-shell-card">
            @include('product-setup.partials._tabs')

            @if($activeTab === 'products')
                @include('product-setup.partials.tabs._products')
            @elseif($activeTab === 'types')
                @include('product-setup.partials.tabs._types')
            @elseif($activeTab === 'categories')
                @include('product-setup.partials.tabs._categories')
            @elseif($activeTab === 'units')
                @include('product-setup.partials.tabs._units')
            @elseif($activeTab === 'structure')
                @include('product-setup.partials.tabs._structure')
            @elseif($activeTab === 'prices')
                @include('product-setup.partials.tabs._prices')
            @endif
        </div>
    </div>

    @include('product-setup.partials.modals._export')
    @include('product-setup.partials.modals._import')

    @can('product.manage')
        @include('product-setup.partials.modals._create_type')
        @include('product-setup.partials.modals._create_category')
        @include('product-setup.partials.modals._create_unit')
        @include('product-setup.partials.modals._create_product')

        @include('product-setup.partials.modals._product_modals')
        @include('product-setup.partials.modals._edit_type_modals')
        @include('product-setup.partials.modals._edit_category_modals')
        @include('product-setup.partials.modals._edit_unit_modals')
    @endcan

    @include('product-setup.partials._scripts')
@endsection