<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\QuickSearchController as WebQuickSearchController;

class QuickSearchController extends ApiController
{
    public function __invoke(): mixed
    {
        return $this->callWeb(WebQuickSearchController::class, '__invoke');
    }
}
