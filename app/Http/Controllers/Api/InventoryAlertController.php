<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\DelegatesToWebController;
use App\Http\Controllers\InventoryAlertController as WebInventoryAlertController;

class InventoryAlertController extends ApiController
{
    use DelegatesToWebController;

    protected string $webController = WebInventoryAlertController::class;

    public function index(): mixed
    {
        return $this->fromWeb('index');
    }

    public function markRead(): mixed
    {
        return $this->fromWeb('markRead');
    }

    public function resolve(): mixed
    {
        return $this->fromWeb('resolve');
    }

    public function ignore(): mixed
    {
        return $this->fromWeb('ignore');
    }

    public function generate(): mixed
    {
        return $this->fromWeb('generate');
    }
}
