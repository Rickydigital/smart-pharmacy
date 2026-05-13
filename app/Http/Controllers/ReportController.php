<?php

namespace App\Http\Controllers;

use App\Services\Reports\ReportDataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:report.view', only: ['index', 'export']),
            new Middleware('permission:report.sales', only: ['sales']),
            new Middleware('permission:report.stock', only: ['stock']),
            new Middleware('permission:report.purchase', only: ['purchases']),
            new Middleware('permission:report.profit', only: ['profit']),
            new Middleware('permission:report.expense', only: ['expenses']),
            new Middleware('permission:report.prescription', only: ['prescriptions']),
        ];
    }

    public function index(Request $request, ReportDataService $service): View
    {
        return view('reports.index', $service->index($request));
    }

    public function sales(Request $request, ReportDataService $service): View
    {
        return view('reports.sales', $service->sales($request));
    }

    public function stock(Request $request, ReportDataService $service): View
    {
        return view('reports.stock', $service->stock($request));
    }

    public function purchases(Request $request, ReportDataService $service): View
    {
        return view('reports.purchases', $service->purchases($request));
    }

    public function profit(Request $request, ReportDataService $service): View
    {
        return view('reports.profit', $service->profit($request));
    }

    public function expenses(Request $request, ReportDataService $service): View
    {
        return view('reports.expenses', $service->expenses($request));
    }

    public function prescriptions(Request $request, ReportDataService $service): View
    {
        return view('reports.prescriptions', $service->prescriptions($request));
    }

    public function export(
        Request $request,
        string $report,
        ReportDataService $service
    ): BinaryFileResponse|HttpResponse|RedirectResponse {
        return $service->export($request, $report);
    }
}