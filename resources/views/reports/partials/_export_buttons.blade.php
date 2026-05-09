@php
    $exportQuery = request()->query();
@endphp

<div class="d-flex flex-wrap justify-content-end" style="gap: 8px;">
    <a href="{{ route('reports.export', array_merge(['report' => $reportKey, 'format' => 'pdf'], $exportQuery)) }}"
       target="_blank"
       class="btn btn-light">
        <i class="mdi mdi-file-pdf-box mr-1"></i>
        PDF
    </a>

    <a href="{{ route('reports.export', array_merge(['report' => $reportKey, 'format' => 'excel'], $exportQuery)) }}"
       class="btn btn-success">
        <i class="mdi mdi-file-excel-box mr-1"></i>
        Excel
    </a>
</div>