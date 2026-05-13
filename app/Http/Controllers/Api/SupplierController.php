<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\DelegatesToWebController;
use App\Http\Controllers\SupplierController as WebSupplierController;
use App\Models\Supplier;

class SupplierController extends ApiController
{
    use DelegatesToWebController;

    protected string $webController = WebSupplierController::class;

    public function index(): mixed
    {
        return $this->fromWeb('index');
    }

    public function store(): mixed
    {
        return $this->fromWeb('store');
    }

    public function update(Supplier $supplier): mixed
    {
        return $this->fromWeb('update');
    }

    public function toggle(Supplier $supplier): mixed
    {
        return $this->fromWeb('toggle');
    }

    public function destroy(Supplier $supplier): mixed
    {
        return $this->fromWeb('destroy');
    }
}