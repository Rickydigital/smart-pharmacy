<div class="nav nav-pills ps-strip">
    <a class="nav-link {{ $activeTab === 'products' ? 'active' : '' }}" href="{{ $tabRoutes['products'] }}">
        <i class="mdi mdi-pill mr-1"></i> Products
    </a>

    <a class="nav-link {{ $activeTab === 'types' ? 'active' : '' }}" href="{{ $tabRoutes['types'] }}">
        <i class="mdi mdi-shape-outline mr-1"></i> Types
    </a>

    <a class="nav-link {{ $activeTab === 'categories' ? 'active' : '' }}" href="{{ $tabRoutes['categories'] }}">
        <i class="mdi mdi-tag-multiple-outline mr-1"></i> Categories
    </a>

    <a class="nav-link {{ $activeTab === 'units' ? 'active' : '' }}" href="{{ $tabRoutes['units'] }}">
        <i class="mdi mdi-ruler-square mr-1"></i> Units
    </a>

    <a class="nav-link {{ $activeTab === 'structure' ? 'active' : '' }}" href="{{ $tabRoutes['structure'] }}">
        <i class="mdi mdi-sitemap-outline mr-1"></i> Package Structure
    </a>

    <a class="nav-link {{ $activeTab === 'prices' ? 'active' : '' }}" href="{{ $tabRoutes['prices'] }}">
        <i class="mdi mdi-cash-multiple mr-1"></i> Prices
    </a>
</div>