<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        return Redirect::route('profile.edit')
            ->with('success', 'Profile updated successfully.');
    }

    public function businessCard()
{
    $pdf = Pdf::loadView('pdf.pharmacy-business-card', [
        'name' => 'Ricky Kinyamagoha',
        'position' => 'Managing Pharmacist',
        'phone' => '+255 624 592 725',
        'email' => 'info@smartpharmacy.co.tz',
        'website' => 'www.smartpharmacy.co.tz',
        'address' => 'Dar es Salaam, Tanzania',
    ])->setPaper('a4', 'landscape');

    return $pdf->stream('smart-pharmacy-business-card.pdf');
}
}