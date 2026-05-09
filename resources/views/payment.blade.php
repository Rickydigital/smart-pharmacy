@extends('components.main-layout')

@section('title', 'Payments')
@section('page-title', 'Payment Records')
@section('page-subtitle', 'Track receipts, pending payments, methods and reconciliation status.')

@section('content')
@php
    $payments = [
        ['ref' => 'PAY-2025-001', 'payer' => 'Jane Cooper', 'method' => 'Cash', 'amount' => '125,000', 'status' => 'Paid', 'sync' => 'Posted'],
        ['ref' => 'PAY-2025-002', 'payer' => 'Cody Fisher', 'method' => 'Mobile Money', 'amount' => '89,500', 'status' => 'Pending', 'sync' => 'Waiting'],
        ['ref' => 'PAY-2025-003', 'payer' => 'Esther Howard', 'method' => 'Bank Transfer', 'amount' => '240,000', 'status' => 'Paid', 'sync' => 'Posted'],
        ['ref' => 'PAY-2025-004', 'payer' => 'Wade Warren', 'method' => 'Card', 'amount' => '61,000', 'status' => 'Failed', 'sync' => 'Failed'],
    ];
@endphp

<x-super-table
    variant="payments"
    title="Payment Table Pro"
    subtitle="A premium financial table for payment tracking, receipts and reconciliation."
    count="4,862"
    add-label="Record Payment"
    create-modal-id="createPaymentModal"
    export-modal-id="exportPaymentsModal"
>
    <x-slot:summary>
        <div class="super-summary-grid">
            <div class="super-summary-card">
                <span class="super-summary-icon blue"><i class="mdi mdi-cash-multiple"></i></span>
                <div>
                    <div class="super-summary-label">Total Collected</div>
                    <div class="super-summary-value">TZS 12.8M</div>
                    <div class="super-summary-change up">↑ 11.2%</div>
                </div>
            </div>

            <div class="super-summary-card">
                <span class="super-summary-icon green"><i class="mdi mdi-check-circle-outline"></i></span>
                <div>
                    <div class="super-summary-label">Paid</div>
                    <div class="super-summary-value">3,920</div>
                    <div class="super-summary-change up">↑ 8.5%</div>
                </div>
            </div>

            <div class="super-summary-card">
                <span class="super-summary-icon orange"><i class="mdi mdi-clock-outline"></i></span>
                <div>
                    <div class="super-summary-label">Pending</div>
                    <div class="super-summary-value">642</div>
                    <div class="super-summary-change down">↓ 2.4%</div>
                </div>
            </div>

            <div class="super-summary-card">
                <span class="super-summary-icon purple"><i class="mdi mdi-receipt-text-outline"></i></span>
                <div>
                    <div class="super-summary-label">Receipts</div>
                    <div class="super-summary-value">4,862</div>
                    <div class="super-summary-change up">↑ 9.1%</div>
                </div>
            </div>
        </div>
    </x-slot:summary>

    <x-slot:filters>
        <div class="super-filter-row">
            <div class="super-search">
                <i class="mdi mdi-magnify"></i>
                <input placeholder="Search payment, payer, reference...">
            </div>

            <select class="super-filter-control">
                <option>Method: All</option>
                <option>Cash</option>
                <option>Mobile Money</option>
                <option>Bank Transfer</option>
                <option>Card</option>
            </select>

            <select class="super-filter-control">
                <option>Status: All</option>
                <option>Paid</option>
                <option>Pending</option>
                <option>Failed</option>
            </select>

            <button class="super-btn"><i class="mdi mdi-calendar-month-outline"></i> Today</button>
            <button class="super-btn text-primary"><i class="mdi mdi-close"></i> Clear Filters</button>
        </div>
    </x-slot:filters>

    <x-slot:tabs>
        <div class="super-tabs">
            <button class="super-tab active">All Payments <span class="super-tab-count">4,862</span></button>
            <button class="super-tab">Paid <span class="super-tab-count">3,920</span></button>
            <button class="super-tab">Pending <span class="super-tab-count">642</span></button>
            <button class="super-tab">Failed <span class="super-tab-count">84</span></button>
            <button class="super-tab">Reversed <span class="super-tab-count">12</span></button>
        </div>
    </x-slot:tabs>

    <x-slot:toolbar>
        <button class="super-btn"><i class="mdi mdi-printer-outline"></i> Print Receipts</button>
        <button class="super-btn"><i class="mdi mdi-bank-sync"></i> Reconcile</button>
    </x-slot:toolbar>

    <x-slot:table>
        <table class="super-table">
            <thead>
                <tr>
                    <th><input class="super-checkbox" type="checkbox"></th>
                    <th>Payment</th>
                    <th>Payer</th>
                    <th>Method</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Ledger Sync</th>
                    <th>Paid At</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $payment)
                    <tr>
                        <td><input class="super-checkbox" type="checkbox"></td>
                        <td>
                            <div class="super-item">
                                <span class="super-thumb"><i class="mdi mdi-receipt-text-outline"></i></span>
                                <div>
                                    <div class="super-item-title">{{ $payment['ref'] }}</div>
                                    <div class="super-item-sub">Receipt ready</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $payment['payer'] }}</td>
                        <td><span class="super-badge badge-blue">{{ $payment['method'] }}</span></td>
                        <td><span class="payment-amount">TZS {{ $payment['amount'] }}</span></td>
                        <td>
                            <span class="super-badge {{ $payment['status'] === 'Paid' ? 'badge-green' : ($payment['status'] === 'Pending' ? 'badge-yellow' : 'badge-red') }}">
                                {{ $payment['status'] }}
                            </span>
                        </td>
                        <td><span class="super-badge {{ $payment['sync'] === 'Posted' ? 'badge-green' : ($payment['sync'] === 'Waiting' ? 'badge-orange' : 'badge-red') }}">{{ $payment['sync'] }}</span></td>
                        <td>May 18, 2025</td>
                        <td class="text-end">
                            <button class="super-action-btn" data-bs-toggle="modal" data-bs-target="#showPaymentModal">
                                <i class="mdi mdi-eye-outline"></i>
                            </button>
                            <button class="super-action-btn" data-bs-toggle="modal" data-bs-target="#editPaymentModal">
                                <i class="mdi mdi-pencil-outline"></i>
                            </button>
                            <button class="super-action-btn">
                                <i class="mdi mdi-printer-outline"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-slot:table>

    <x-slot:mobile>
        @foreach($payments as $payment)
            <div class="super-mobile-card">
                <div class="super-mobile-card-head">
                    <div>
                        <div class="super-item-title">{{ $payment['ref'] }}</div>
                        <div class="super-item-sub">{{ $payment['payer'] }} · TZS {{ $payment['amount'] }}</div>
                    </div>
                    <span class="super-badge {{ $payment['status'] === 'Paid' ? 'badge-green' : 'badge-yellow' }}">{{ $payment['status'] }}</span>
                </div>
            </div>
        @endforeach
    </x-slot:mobile>

    <x-slot:modals>
        <div class="modal fade super-modal" id="createPaymentModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <form class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Record Payment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="super-form-label">Payer Name</label>
                                <input class="form-control super-form-control" placeholder="Enter payer name">
                            </div>

                            <div class="col-md-6">
                                <label class="super-form-label">Amount</label>
                                <input class="form-control super-form-control" placeholder="TZS 0.00">
                            </div>

                            <div class="col-md-6">
                                <label class="super-form-label">Method</label>
                                <select class="form-control super-form-control">
                                    <option>Cash</option>
                                    <option>Mobile Money</option>
                                    <option>Bank Transfer</option>
                                    <option>Card</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="super-form-label">Status</label>
                                <select class="form-control super-form-control">
                                    <option>Paid</option>
                                    <option>Pending</option>
                                    <option>Failed</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0">
                        <button type="button" class="super-btn" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="super-btn super-btn-primary">Save Payment</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal fade super-modal" id="showPaymentModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Payment Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="super-item mb-3">
                            <span class="super-thumb"><i class="mdi mdi-receipt-text-outline"></i></span>
                            <div>
                                <div class="super-item-title">PAY-2025-001</div>
                                <div class="super-item-sub">Jane Cooper · TZS 125,000</div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-6">
                                <div class="text-muted fw-bold small">Method</div>
                                <div class="fw-bold">Cash</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted fw-bold small">Status</div>
                                <span class="super-badge badge-green">Paid</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade super-modal" id="editPaymentModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <form class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Payment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="super-form-label">Payer Name</label>
                                <input class="form-control super-form-control" value="Jane Cooper">
                            </div>

                            <div class="col-md-6">
                                <label class="super-form-label">Amount</label>
                                <input class="form-control super-form-control" value="125000">
                            </div>

                            <div class="col-md-6">
                                <label class="super-form-label">Method</label>
                                <select class="form-control super-form-control">
                                    <option selected>Cash</option>
                                    <option>Mobile Money</option>
                                    <option>Bank Transfer</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="super-form-label">Status</label>
                                <select class="form-control super-form-control">
                                    <option selected>Paid</option>
                                    <option>Pending</option>
                                    <option>Failed</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0">
                        <button type="button" class="super-btn" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="super-btn super-btn-primary">Update Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </x-slot:modals>
</x-super-table>
@endsection