@extends('components.main-layout')

@section('title', 'Users')
@section('page-title', 'Users')
@section('page-subtitle', 'Manage system users with advanced filters and actions.')

@section('content')
@php
    $users = [
        ['name' => 'Jane Cooper', 'code' => 'USR-1001', 'role' => 'Administrator', 'email' => 'jane@example.com', 'status' => 'Active', 'created' => 'May 18, 2025'],
        ['name' => 'Cody Fisher', 'code' => 'USR-1002', 'role' => 'Manager', 'email' => 'cody@example.com', 'status' => 'Active', 'created' => 'May 17, 2025'],
        ['name' => 'Esther Howard', 'code' => 'USR-1003', 'role' => 'Cashier', 'email' => 'esther@example.com', 'status' => 'Pending', 'created' => 'May 16, 2025'],
        ['name' => 'Wade Warren', 'code' => 'USR-1004', 'role' => 'Stock Officer', 'email' => 'wade@example.com', 'status' => 'Blocked', 'created' => 'May 15, 2025'],
    ];
@endphp

<x-super-table
    variant="advanced"
    title="Advanced Table View"
    subtitle="A powerful, flexible table for managing users and permissions."
    count="1,248"
    add-label="Add User"
    create-modal-id="createUserModal"
    export-modal-id="exportUsersModal"
>
    <x-slot:actions>
        <button type="button" class="super-btn">
            <i class="mdi mdi-view-column-outline"></i>
            Columns
        </button>
    </x-slot:actions>

    <x-slot:filters>
        <div class="super-filter-row">
            <div class="super-search">
                <i class="mdi mdi-magnify"></i>
                <input type="text" placeholder="Search users...">
            </div>

            <select class="super-filter-control">
                <option>Role: All</option>
                <option>Administrator</option>
                <option>Manager</option>
                <option>Cashier</option>
            </select>

            <select class="super-filter-control">
                <option>Status: All</option>
                <option>Active</option>
                <option>Pending</option>
                <option>Blocked</option>
            </select>

            <button class="super-btn">
                <i class="mdi mdi-calendar-month-outline"></i>
                May 12 - May 18, 2025
            </button>

            <button class="super-btn text-primary">
                <i class="mdi mdi-close"></i>
                Clear Filters
            </button>
        </div>
    </x-slot:filters>

    <x-slot:tabs>
        <div class="super-tabs">
            <button class="super-tab active">All Users <span class="super-tab-count">1,248</span></button>
            <button class="super-tab">Active <span class="super-tab-count">982</span></button>
            <button class="super-tab">Pending <span class="super-tab-count">156</span></button>
            <button class="super-tab">Blocked <span class="super-tab-count">110</span></button>
        </div>
    </x-slot:tabs>

    <x-slot:toolbar>
        <button class="super-btn">
            <i class="mdi mdi-filter-variant"></i>
            More Filters
        </button>
    </x-slot:toolbar>

    <x-slot:table>
        <table class="super-table">
            <thead>
                <tr>
                    <th><input type="checkbox" class="super-checkbox"></th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $index => $user)
                    <tr>
                        <td><input type="checkbox" class="super-checkbox"></td>
                        <td>
                            <div class="super-item">
                                <span class="super-thumb">
                                    <i class="mdi mdi-account-outline"></i>
                                </span>
                                <div>
                                    <div class="super-item-title">{{ $user['name'] }}</div>
                                    <div class="super-item-sub">{{ $user['code'] }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $user['email'] }}</td>
                        <td><span class="super-badge badge-blue">{{ $user['role'] }}</span></td>
                        <td>
                            <span class="super-badge {{ $user['status'] === 'Active' ? 'badge-green' : ($user['status'] === 'Pending' ? 'badge-yellow' : 'badge-red') }}">
                                {{ $user['status'] }}
                            </span>
                        </td>
                        <td>{{ $user['created'] }}</td>
                        <td class="text-end">
                            <button class="super-action-btn" data-bs-toggle="modal" data-bs-target="#showUserModal">
                                <i class="mdi mdi-eye-outline"></i>
                            </button>
                            <button class="super-action-btn" data-bs-toggle="modal" data-bs-target="#editUserModal">
                                <i class="mdi mdi-pencil-outline"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-slot:table>

    <x-slot:mobile>
        @foreach($users as $user)
            <div class="super-mobile-card">
                <div class="super-mobile-card-head">
                    <div class="super-item">
                        <span class="super-thumb"><i class="mdi mdi-account-outline"></i></span>
                        <div>
                            <div class="super-item-title">{{ $user['name'] }}</div>
                            <div class="super-item-sub">{{ $user['email'] }}</div>
                        </div>
                    </div>
                    <span class="super-badge badge-green">{{ $user['status'] }}</span>
                </div>
            </div>
        @endforeach
    </x-slot:mobile>

    <x-slot:modals>
        @include('users.partials.user-modals')
    </x-slot:modals>
</x-super-table>
@endsection