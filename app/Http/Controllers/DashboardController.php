<?php

namespace App\Http\Controllers;

use App\Services\Dashboard\DashboardDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardDataService $dashboard): View
    {
        $data = $dashboard->build($request, Auth::user());

        return view('dashboard', $data);
    }
}