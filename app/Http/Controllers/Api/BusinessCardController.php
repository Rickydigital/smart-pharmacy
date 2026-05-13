<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BusinessCardController as WebBusinessCardController;

class BusinessCardController extends ApiController
{
    public function profile(): mixed
    {
        $user = request()->user()->load(['roles', 'branch.pharmacy.setting', 'pharmacy.setting']);
        $pharmacy = $user->pharmacy ?: $user->branch?->pharmacy;

        return $this->success([
            'user' => $user,
            'pharmacy' => $pharmacy,
            'branch' => $user->branch,
            'settings' => $pharmacy?->setting,
            'download_endpoint' => '/api/v1/business-card/download',
            'print_endpoint' => '/api/v1/business-card/print',
        ]);
    }

    public function download(): mixed
    {
        return $this->callWeb(WebBusinessCardController::class, 'downloadBusinessCardPdf');
    }

    public function print(): mixed
    {
        return $this->callWeb(WebBusinessCardController::class, 'printBusinessCardPdf');
    }
}
