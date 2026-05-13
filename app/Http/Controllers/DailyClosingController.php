<?php

namespace App\Http\Controllers;

use App\Models\DailyClosing;
use App\Services\DailyClosing\DailyClosingDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DailyClosingController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:daily_closing.view', only: ['index', 'calculate']),
            new Middleware('permission:daily_closing.create', only: ['store']),
            new Middleware('permission:daily_closing.submit', only: ['submit']),
            new Middleware('permission:daily_closing.verify', only: ['verify', 'recalculate']),
            new Middleware('permission:daily_closing.reject', only: ['reject']),
        ];
    }

    public function index(Request $request, DailyClosingDataService $service): View
    {
        return view('daily-closings.index', $service->index($request, Auth::user()));
    }

    public function calculate(Request $request, DailyClosingDataService $service): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'totals' => $service->calculate($request),
        ]);
    }

    public function store(Request $request, DailyClosingDataService $service): RedirectResponse
    {
        $closing = $service->store($request, Auth::user());

        return back()->with('success', $closing->status === 'submitted'
            ? 'Branch daily closing submitted successfully.'
            : 'Branch daily closing saved as draft.');
    }

    public function submit(DailyClosing $dailyClosing, DailyClosingDataService $service): RedirectResponse
    {
        $service->submit($dailyClosing, Auth::user());

        return back()->with('success', 'Daily closing submitted successfully.');
    }

    public function verify(DailyClosing $dailyClosing, DailyClosingDataService $service): RedirectResponse
    {
        $service->verify($dailyClosing, Auth::user());

        return back()->with('success', 'Daily closing verified successfully.');
    }

    public function reject(Request $request, DailyClosing $dailyClosing, DailyClosingDataService $service): RedirectResponse
    {
        $service->reject($request, $dailyClosing, Auth::user());

        return back()->with('success', 'Daily closing rejected successfully.');
    }

    public function recalculate(DailyClosing $dailyClosing, DailyClosingDataService $service): RedirectResponse
    {
        $service->recalculate($dailyClosing, Auth::user());

        return back()->with('success', 'Daily closing recalculated and submitted for verification.');
    }
}