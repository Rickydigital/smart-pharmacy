<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ProductSetup\ProductCategoryController as WebProductCategoryController;
use App\Http\Controllers\ProductSetup\ProductController as WebProductController;
use App\Http\Controllers\ProductSetup\ProductPriceController as WebProductPriceController;
use App\Http\Controllers\ProductSetup\ProductTypeController as WebProductTypeController;
use App\Http\Controllers\ProductSetup\ProductUnitController as WebProductUnitController;
use App\Http\Controllers\ProductSetup\UnitController as WebUnitController;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductPrice;
use App\Models\ProductType;
use App\Models\ProductUnit;
use App\Models\Unit;

class ProductSetupController extends ApiController
{
    public function index(): mixed
    {
        return $this->callWeb(WebProductController::class, 'index');
    }

    public function export(): mixed
    {
        return $this->callWeb(WebProductController::class, 'export');
    }

    public function import(): mixed
    {
        return $this->callWeb(WebProductController::class, 'import');
    }

    public function storeType(): mixed
    {
        return $this->callWeb(WebProductTypeController::class, 'store');
    }

    public function updateType(ProductType $productType): mixed
    {
        return $this->callWeb(WebProductTypeController::class, 'update');
    }

    public function toggleType(ProductType $productType): mixed
    {
        return $this->callWeb(WebProductTypeController::class, 'toggle');
    }

    public function destroyType(ProductType $productType): mixed
    {
        return $this->callWeb(WebProductTypeController::class, 'destroy');
    }

    public function storeCategory(): mixed
    {
        return $this->callWeb(WebProductCategoryController::class, 'store');
    }

    public function updateCategory(ProductCategory $productCategory): mixed
    {
        return $this->callWeb(WebProductCategoryController::class, 'update');
    }

    public function toggleCategory(ProductCategory $productCategory): mixed
    {
        return $this->callWeb(WebProductCategoryController::class, 'toggle');
    }

    public function destroyCategory(ProductCategory $productCategory): mixed
    {
        return $this->callWeb(WebProductCategoryController::class, 'destroy');
    }

    public function storeUnit(): mixed
    {
        return $this->callWeb(WebUnitController::class, 'store');
    }

    public function updateUnit(Unit $unit): mixed
    {
        return $this->callWeb(WebUnitController::class, 'update');
    }

    public function toggleUnit(Unit $unit): mixed
    {
        return $this->callWeb(WebUnitController::class, 'toggle');
    }

    public function destroyUnit(Unit $unit): mixed
    {
        return $this->callWeb(WebUnitController::class, 'destroy');
    }

    public function storeProduct(): mixed
    {
        return $this->callWeb(WebProductController::class, 'store');
    }

    public function updateProduct(Product $product): mixed
    {
        return $this->callWeb(WebProductController::class, 'update');
    }

    public function toggleProduct(Product $product): mixed
    {
        return $this->callWeb(WebProductController::class, 'toggle');
    }

    public function destroyProduct(Product $product): mixed
    {
        return $this->callWeb(WebProductController::class, 'destroy');
    }

    public function storeProductUnit(): mixed
    {
        return $this->callWeb(WebProductUnitController::class, 'store');
    }

    public function updateProductUnit(ProductUnit $productUnit): mixed
    {
        return $this->callWeb(WebProductUnitController::class, 'update');
    }

    public function toggleProductUnit(ProductUnit $productUnit): mixed
    {
        return $this->callWeb(WebProductUnitController::class, 'toggle');
    }

    public function makeDefaultSaleUnit(ProductUnit $productUnit): mixed
    {
        return $this->callWeb(WebProductUnitController::class, 'makeDefaultSaleUnit');
    }

    public function destroyProductUnit(ProductUnit $productUnit): mixed
    {
        return $this->callWeb(WebProductUnitController::class, 'destroy');
    }

    public function storePrice(): mixed
    {
        return $this->callWeb(WebProductPriceController::class, 'store');
    }

    public function updatePrice(ProductPrice $productPrice): mixed
    {
        return $this->callWeb(WebProductPriceController::class, 'update');
    }

    public function togglePrice(ProductPrice $productPrice): mixed
    {
        return $this->callWeb(WebProductPriceController::class, 'toggle');
    }

    public function destroyPrice(ProductPrice $productPrice): mixed
    {
        return $this->callWeb(WebProductPriceController::class, 'destroy');
    }
}