<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class BusinessCardController extends Controller
{
    public function downloadBusinessCardPdf(Request $request)
    {
        $user = $request->user()->load([
            'roles',
            'branch.pharmacy.setting',
            'pharmacy.setting',
        ]);

        $pharmacy = $user->pharmacy
            ?: $user->branch?->pharmacy;

        $branch = $user->branch;

        $settings = $pharmacy?->setting;

        $primaryColor = $settings?->primary_color
            ?: '#071f3c';

        $secondaryColor = $settings?->secondary_color
            ?: '#d3a34d';

        $fileName = 'business-card-' . ($user->username ?: $user->id) . '.pdf';

        $pdf = Pdf::loadView('profile.card1', [
            'user' => $user,
            'pharmacy' => $pharmacy,
            'branch' => $branch,
            'settings' => $settings,
            'primaryColor' => $primaryColor,
            'secondaryColor' => $secondaryColor,
        ]);

        // Exact business card size: 3.5in × 2in = 252pt × 144pt
        // Page 1 = front, Page 2 = back
        $pdf->setPaper([0, 0, 252, 144], 'landscape');

        return $pdf->download($fileName);
    }

    public function printBusinessCardPdf(Request $request)
    {
        $user = $request->user()->load([
            'roles',
            'branch.pharmacy.setting',
            'pharmacy.setting',
        ]);

        $pharmacy = $user->pharmacy
            ?: $user->branch?->pharmacy;

        $branch = $user->branch;

        $settings = $pharmacy?->setting;

        $primaryColor = $settings?->primary_color
            ?: '#071f3c';

        $secondaryColor = $settings?->secondary_color
            ?: '#d3a34d';

        $pdf = Pdf::loadView('profile.card1', [
            'user' => $user,
            'pharmacy' => $pharmacy,
            'branch' => $branch,
            'settings' => $settings,
            'primaryColor' => $primaryColor,
            'secondaryColor' => $secondaryColor,
        ]);

        // Exact business card size: 3.5in × 2in = 252pt × 144pt
        // PDF page 1 = front, page 2 = back
        $pdf->setPaper([0, 0, 252, 144], 'landscape');

        return $pdf->stream('business-card-print.pdf');
    }
}