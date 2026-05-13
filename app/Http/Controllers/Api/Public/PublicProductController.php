<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductType;
use App\Models\PublicProductSearchLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicProductController extends Controller
{
    public function types(Branch $branch): JsonResponse
    {
        $types = ProductType::query()
            ->where('pharmacy_id', $branch->pharmacy_id)
            ->where('is_active', true)
            ->whereHas('products', fn ($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json([
            'success' => true,
            'data' => $types,
        ]);
    }

    public function categories(Request $request, Branch $branch): JsonResponse
    {
        $validated = $request->validate([
            'type_id' => ['nullable', 'integer', 'exists:product_types,id'],
        ]);

        $categories = ProductCategory::query()
            ->where('pharmacy_id', $branch->pharmacy_id)
            ->where('is_active', true)
            ->when($validated['type_id'] ?? null, function ($query, $typeId) {
                $query->where('product_type_id', $typeId);
            })
            ->whereHas('products', fn ($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'product_type_id']);

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    public function index(Request $request, Branch $branch): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'type_id' => ['nullable', 'integer', 'exists:product_types,id'],
            'category_id' => ['nullable', 'integer', 'exists:product_categories,id'],
        ]);

        $queryText = trim((string) ($validated['q'] ?? ''));

        $products = Product::query()
            ->with(['category:id,name', 'productType:id,name'])
            ->where('pharmacy_id', $branch->pharmacy_id)
            ->where('is_active', true)
            ->when($validated['type_id'] ?? null, fn ($q, $typeId) => $q->where('product_type_id', $typeId))
            ->when($validated['category_id'] ?? null, fn ($q, $categoryId) => $q->where('product_category_id', $categoryId))
            ->when($queryText !== '', function ($builder) use ($queryText) {
                $builder->where(function ($builder) use ($queryText) {
                    $builder->where('name', 'like', "%{$queryText}%")
                        ->orWhere('generic_name', 'like', "%{$queryText}%")
                        ->orWhere('brand', 'like', "%{$queryText}%")
                        ->orWhere('strength', 'like', "%{$queryText}%")
                        ->orWhere('code', 'like', "%{$queryText}%")
                        ->orWhere('barcode', 'like', "%{$queryText}%");
                });
            })
            ->orderBy('name')
            ->limit(50)
            ->get()
            ->map(fn ($product) => $this->productPayload($product, $branch));

        $this->logSearch(
            request: $request,
            branch: $branch,
            product: null,
            query: $queryText ?: null,
            status: $products->isEmpty() ? 'no_results' : 'product_list',
            resultsCount: $products->count(),
            meta: [
                'type_id' => $validated['type_id'] ?? null,
                'category_id' => $validated['category_id'] ?? null,
            ]
        );

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    public function search(Request $request, Branch $branch): JsonResponse
    {
        return $this->index($request, $branch);
    }

    public function availability(Request $request, Branch $branch, Product $product): JsonResponse
    {
        abort_if($product->pharmacy_id !== $branch->pharmacy_id, 404);

        $available = $this->hasAvailableStock($product->id, $branch->id);

        $alternatives = collect();

        if (! $available && $product->product_category_id) {
            $alternatives = Product::query()
                ->with(['category:id,name', 'productType:id,name'])
                ->where('pharmacy_id', $branch->pharmacy_id)
                ->where('is_active', true)
                ->where('id', '!=', $product->id)
                ->where('product_category_id', $product->product_category_id)
                ->whereHas('inventories', function ($query) use ($branch) {
                    $query->where('branch_id', $branch->id)
                        ->where('is_active', true)
                        ->where('status', 'available')
                        ->where('available_quantity_base_units', '>', 0);
                })
                ->orderBy('name')
                ->limit(10)
                ->get()
                ->map(fn ($alternative) => $this->productPayload($alternative, $branch));
        }

        $this->logSearch(
            request: $request,
            branch: $branch,
            product: $product,
            query: $product->name,
            status: $available ? 'available' : 'not_available',
            resultsCount: $alternatives->count()
        );

        return response()->json([
            'success' => true,
            'data' => [
                'available' => $available,
                'status' => $available ? 'available' : 'not_available',
                'message' => $available
                    ? 'Medicine is available. Please call pharmacy to confirm before visiting.'
                    : 'Medicine is not available. See available alternatives in the same category.',
                'product' => $this->productPayload($product->loadMissing(['category:id,name', 'productType:id,name']), $branch),
                'alternatives' => $alternatives,
            ],
        ]);
    }

    private function productPayload(Product $product, Branch $branch): array
    {
        $available = $this->hasAvailableStock($product->id, $branch->id);

        return [
            'id' => $product->id,
            'name' => $product->name,
            'code' => $product->code,
            'generic_name' => $product->generic_name,
            'brand' => $product->brand,
            'strength' => $product->strength,
            'requires_prescription' => (bool) $product->requires_prescription,
            'type' => $product->productType ? [
                'id' => $product->productType->id,
                'name' => $product->productType->name,
            ] : null,
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->name,
            ] : null,
            'available' => $available,
            'stock_status' => $available ? 'available' : 'not_available',
        ];
    }

    private function hasAvailableStock(int $productId, int $branchId): bool
    {
        return Inventory::query()
            ->where('branch_id', $branchId)
            ->where('product_id', $productId)
            ->where('is_active', true)
            ->where('status', 'available')
            ->where('available_quantity_base_units', '>', 0)
            ->exists();
    }

    private function logSearch(
        Request $request,
        Branch $branch,
        ?Product $product,
        ?string $query,
        string $status,
        int $resultsCount,
        array $meta = []
    ): void {
        PublicProductSearchLog::query()->create([
            'pharmacy_id' => $branch->pharmacy_id,
            'branch_id' => $branch->id,
            'product_id' => $product?->id,
            'query' => $query,
            'source' => 'smart_app',
            'result_status' => $status,
            'results_count' => $resultsCount,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
            'meta' => [
                'branch_name' => $branch->name,
                ...$meta,
            ],
        ]);
    }
}