<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ProfileController as WebProfileController;

class ProfileController extends ApiController
{
    public function show(): mixed
    {
        return $this->success(request()->user()->load(['pharmacy', 'branch', 'roles']));
    }

    public function update(): mixed
    {
        return $this->callWeb(WebProfileController::class, 'update');
    }
}
