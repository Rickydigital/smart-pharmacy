@extends('components.main-layout')

@section('title', 'Records & Insights')
@section('page-title', 'Records & Insights')

@section('content')
<x-super-table
    variant="records-insights"
    title="Records & Insights"
    subtitle="Manage, analyze, and track all records in one place."
    count="12,842"
    add-label="Add Record"
    create-modal-id="createRecordModal"
    export-modal-id="exportRecordsModal"
>
    <x-slot:summary>
        <div class="super-summary-grid">
            <div class="super-summary-card">
                <span class="super-summary-icon blue"><i class="mdi mdi-chart-bar"></i></span>
                <div>
                    <div class="super-summary-label">Total Records</div>
                    <div class="super-summary-value">12,842</div>
                    <div class="super-summary-change up">↑ 18.6%</div>
                </div>
            </div>

            <div class="super-summary-card">
                <span class="super-summary-icon green"><i class="mdi mdi-check"></i></span>
                <div>
                    <div class="super-summary-label">Active Records</div>
                    <div class="super-summary-value">8,532</div>
                    <div class="super-summary-change up">↑ 14.3%</div>
                </div>
            </div>

            <div class="super-summary-card">
                <span class="super-summary-icon orange"><i class="mdi mdi-timer-sand"></i></span>
                <div>
                    <div class="super-summary-label">Pending Records</div>
                    <div class="super-summary-value">2,156</div>
                    <div class="super-summary-change down">↓ 5.7%</div>
                </div>
            </div>

            <div class="super-summary-card">
                <span class="super-summary-icon purple"><i class="mdi mdi-archive-outline"></i></span>
                <div>
                    <div class="super-summary-label">Archived Records</div>
                    <div class="super-summary-value">2,154</div>
                    <div class="super-summary-change up">↑ 7.2%</div>
                </div>
            </div>
        </div>
    </x-slot:summary>

    <x-slot:filters>
        <div class="super-filter-row">
            <div class="super-search">
                <i class="mdi mdi-magnify"></i>
                <input placeholder="Search by name, ID, or keyword...">
            </div>

            <button class="super-btn"><i class="mdi mdi-filter-variant"></i> Filters <span class="super-tab-count">2</span></button>
            <button class="super-btn"><i class="mdi mdi-refresh"></i> Refresh</button>
        </div>
    </x-slot:filters>

    <x-slot:tabs>
        <div class="super-tabs">
            <button class="super-tab active">All Records <span class="super-tab-count">12,842</span></button>
            <button class="super-tab">Active <span class="super-tab-count">8,532</span></button>
            <button class="super-tab">Pending <span class="super-tab-count">2,156</span></button>
            <button class="super-tab">Archived <span class="super-tab-count">2,154</span></button>
        </div>
    </x-slot:tabs>

    <x-slot:table>
        <table class="super-table">
            <thead>
                <tr>
                    <th><input class="super-checkbox" type="checkbox"></th>
                    <th>Record Name</th>
                    <th>Category</th>
                    <th>Owner</th>
                    <th>Created On</th>
                    <th>Progress</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach(['AI Course Blueprint','Q2 Financial Report','Marketing Campaign Plan','New Hire Onboarding','Product Roadmap 2025'] as $i => $name)
                    <tr>
                        <td><input class="super-checkbox" type="checkbox"></td>
                        <td>
                            <div class="super-item">
                                <span class="super-thumb"><i class="mdi mdi-file-document-outline"></i></span>
                                <div>
                                    <div class="super-item-title">{{ $name }}</div>
                                    <div class="super-item-sub">REC-1000{{ $i + 1 }}</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="super-badge badge-purple">Course</span></td>
                        <td><span class="super-person"><span class="super-avatar"></span> Jane Smith</span></td>
                        <td>May {{ 18 - $i }}, 2025</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span>{{ 85 - ($i * 10) }}%</span>
                                <div class="super-progress"><span style="width: {{ 85 - ($i * 10) }}%;"></span></div>
                            </div>
                        </td>
                        <td><span class="super-badge {{ $i == 2 ? 'badge-orange' : 'badge-green' }}">{{ $i == 2 ? 'Pending' : 'Active' }}</span></td>
                        <td class="text-end"><button class="super-action-btn"><i class="mdi mdi-dots-horizontal"></i></button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-slot:table>

    <x-slot:modals>
        @include('users.partials.user-modals')
    </x-slot:modals>
</x-super-table>
@endsection