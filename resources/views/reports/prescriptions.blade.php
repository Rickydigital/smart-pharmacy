@extends('components.main-layout')

@section('title', 'Prescription Report')
@section('page-title', 'Prescription Report')
@section('page-subtitle', 'Prescription report placeholder')

@section('content')
@include('reports.partials._styles')

<div class="container-fluid report-page">
    <div class="card report-hero mb-4">
        @include('reports.partials._export_buttons', ['reportKey' => 'center'])
        <div class="card-body">
            <div class="d-flex align-items-center" style="gap: 13px;">
                <span class="report-icon"><i class="mdi mdi-file-document-edit-outline"></i></span>
                <div>
                    <h4 class="report-title">Prescription Report</h4>
                    <p class="report-subtitle">This report will connect after prescription module is implemented.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="report-card">
        <div class="report-card-header">
            <h5>Prescription Report</h5>
            <p>Ready placeholder.</p>
        </div>

        <div class="report-empty">
            {{ $message }}
        </div>
    </div>
</div>
@endsection