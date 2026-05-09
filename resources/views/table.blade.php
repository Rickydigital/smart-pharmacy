@extends('components.main-layout')

@section('title', 'Data Grid Pro')
@section('page-title', 'Data Grid Pro')

@section('content')
<div class="super-layout-with-panel">
    <x-super-table
        variant="data-grid-pro"
        title="Data Grid Pro"
        subtitle="Advanced data management with powerful filtering and smart controls."
        count="1,248"
        add-label="Add Record"
        create-modal-id="createGridModal"
        export-modal-id="exportGridModal"
    >
        <x-slot:actions>
            <button class="super-btn"><i class="mdi mdi-content-save-outline"></i> Saved Views</button>
            <button class="super-btn"><i class="mdi mdi-import"></i> Import</button>
        </x-slot:actions>

        <x-slot:filters>
            <div class="super-filter-row">
                <div class="super-search">
                    <i class="mdi mdi-magnify"></i>
                    <input placeholder="Search by name, email, or ID...">
                </div>
                <select class="super-filter-control"><option>Course</option></select>
                <select class="super-filter-control"><option>Instructor</option></select>
                <select class="super-filter-control"><option>Status (3)</option></select>
                <button class="super-btn"><i class="mdi mdi-calendar"></i> Date Range</button>
                <button class="super-btn"><i class="mdi mdi-filter-variant"></i> More Filters <span class="super-tab-count">2</span></button>
            </div>

            <div class="super-filter-row">
                <span class="super-filter-chip">Department: Design, Development <i class="mdi mdi-close"></i></span>
                <span class="super-filter-chip">Level: Beginner, Intermediate <i class="mdi mdi-close"></i></span>
                <span class="super-filter-chip">Price: $0 - $200 <i class="mdi mdi-close"></i></span>
                <button class="super-btn text-primary">Clear all</button>
            </div>
        </x-slot:filters>

        <x-slot:toolbar>
            <button class="super-btn"><i class="mdi mdi-check-box-outline"></i> 8 selected</button>
            <button class="super-btn"><i class="mdi mdi-lightning-bolt-outline"></i> Bulk Actions</button>
        </x-slot:toolbar>

        <x-slot:table>
            <table class="super-table">
                <thead>
                    <tr>
                        <th><input class="super-checkbox" type="checkbox"></th>
                        <th>ID</th>
                        <th>Course Name</th>
                        <th>Instructor</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Students</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(range(1, 8) as $i)
                        <tr>
                            <td><input class="super-checkbox" type="checkbox" {{ $i <= 2 ? 'checked' : '' }}></td>
                            <td>#C-1024{{ $i }}</td>
                            <td>
                                <div class="super-item">
                                    <span class="super-thumb"><i class="mdi mdi-school-outline"></i></span>
                                    <div>
                                        <div class="super-item-title">{{ ['UI/UX Design Masterclass','Advanced React Development','Data Science with Python','Product Management 101','Figma Design System','Node.js Backend Essentials','Digital Marketing Strategy','Machine Learning A-Z'][$i-1] }}</div>
                                        <div class="super-item-sub">Course record</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="super-person"><span class="super-avatar"></span> Jane Cooper</span></td>
                            <td><span class="super-badge badge-blue">Design</span></td>
                            <td>$129.00</td>
                            <td>{{ number_format(900 + ($i * 156)) }}</td>
                            <td><span class="super-badge {{ $i == 3 ? 'badge-gray' : 'badge-green' }}">{{ $i == 3 ? 'Draft' : 'Published' }}</span></td>
                            <td>May {{ 19 - $i }}, 2025</td>
                            <td class="text-end"><button class="super-action-btn"><i class="mdi mdi-dots-horizontal"></i></button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-slot:table>

        <x-slot:mobile>
            <div class="super-mobile-card">
                <div class="super-mobile-card-head">
                    <div>
                        <div class="super-item-title">UI/UX Design Masterclass</div>
                        <div class="super-item-sub">Jane Cooper · $129.00</div>
                    </div>
                    <span class="super-badge badge-green">Published</span>
                </div>
            </div>
        </x-slot:mobile>

        <x-slot:modals>
            @include('users.partials.user-modals')
        </x-slot:modals>
    </x-super-table>

    <aside class="super-side-panel">
        <h5 class="fw-black mb-3" style="font-weight:950;color:#0f172a;">Advanced Filters</h5>

        <div class="mb-3">
            <label class="super-form-label">Enrollment Count</label>
            <input class="form-control super-form-control" value="Greater than 100">
        </div>

        <div class="mb-3">
            <label class="super-form-label">Rating</label>
            <input class="form-control super-form-control" value="4.0 ★">
        </div>

        <div class="mb-3">
            <label class="super-form-label">Tags</label>
            <div class="super-filter-row">
                <span class="super-filter-chip">Popular</span>
                <span class="super-filter-chip">Bestseller</span>
                <span class="super-filter-chip">Trending</span>
            </div>
        </div>

        <button class="super-btn super-btn-primary w-100 justify-content-center">
            Apply Filters
        </button>
    </aside>
</div>
@endsection