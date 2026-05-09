<?php

namespace App\Http\Controllers\ProductSetup;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductType;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Exports\ProductSetup\ProductImportTemplateExport;
use App\Imports\ProductSetup\ProductSetupImport;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:product.view', only: ['index']),
            new Middleware('permission:product.manage', except: ['index']),
        ];
    }

    public function index(Request $request): View
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $activeTab = $request->input('tab', 'products');

        $products = Product::query()
            ->with([
                'productType',
                'category',
                'baseUnit',
                'units.unit',
                'units.prices',
                'prices.productUnit.unit',
            ])
            ->where('pharmacy_id', $pharmacy->id)
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->search);

                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('generic_name', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('product_type_id'), function ($query) use ($request) {
                $query->where('product_type_id', $request->product_type_id);
            })
            ->when($request->filled('product_category_id'), function ($query) use ($request) {
                $query->where('product_category_id', $request->product_category_id);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('is_active', $request->status === 'active');
            })
            ->latest()
            ->paginate(15, ['*'], 'products_page')
            ->withQueryString();

        $productTypes = ProductType::query()
            ->withCount(['categories', 'products'])
            ->where('pharmacy_id', $pharmacy->id)
            ->orderBy('name')
            ->paginate(15, ['*'], 'types_page')
            ->appends(array_merge($request->query(), ['tab' => 'types']));

        $categories = ProductCategory::query()
            ->with('productType')
            ->withCount('products')
            ->where('pharmacy_id', $pharmacy->id)
            ->orderBy('name')
            ->paginate(15, ['*'], 'categories_page')
            ->appends(array_merge($request->query(), ['tab' => 'categories']));

        $units = Unit::query()
            ->withCount('productUnits')
            ->where('pharmacy_id', $pharmacy->id)
            ->orderBy('name')
            ->paginate(15, ['*'], 'units_page')
            ->appends(array_merge($request->query(), ['tab' => 'units']));

        $structureProducts = Product::query()
            ->with(['baseUnit', 'units.unit', 'units.prices'])
            ->where('pharmacy_id', $pharmacy->id)
            ->orderBy('name')
            ->paginate(10, ['*'], 'structure_page')
            ->appends(array_merge($request->query(), ['tab' => 'structure']));

        $priceProducts = Product::query()
            ->with(['baseUnit', 'units.unit', 'units.prices'])
            ->where('pharmacy_id', $pharmacy->id)
            ->orderBy('name')
            ->paginate(8, ['*'], 'prices_page')
            ->appends(array_merge($request->query(), ['tab' => 'prices']));

        $typeOptions = ProductType::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $categoryOptions = ProductCategory::query()
            ->with('productType')
            ->where('pharmacy_id', $pharmacy->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $unitOptions = Unit::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $modalProducts = collect($products->items())
            ->merge($structureProducts->items())
            ->merge($priceProducts->items())
            ->unique('id')
            ->values();

        return view('product-setup.index', compact(
            'products',
            'productTypes',
            'categories',
            'units',
            'structureProducts',
            'priceProducts',
            'typeOptions',
            'categoryOptions',
            'unitOptions',
            'modalProducts',
            'activeTab'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $validated = $request->validate([
            'product_type_id' => [
                'required',
                Rule::exists('product_types', 'id')->where('pharmacy_id', $pharmacy->id),
            ],
            'product_category_id' => [
                'nullable',
                Rule::exists('product_categories', 'id')->where('pharmacy_id', $pharmacy->id),
            ],
            'base_unit_id' => [
                'required',
                Rule::exists('units', 'id')->where('pharmacy_id', $pharmacy->id),
            ],
            'name' => ['required', 'string', 'max:180'],
            'generic_name' => ['nullable', 'string', 'max:180'],
            'strength' => ['nullable', 'string', 'max:80'],
            'brand' => ['nullable', 'string', 'max:120'],
            'requires_expiry' => ['nullable', 'boolean'],
            'requires_prescription' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
        ]);

        Product::query()->create([
            'pharmacy_id' => $pharmacy->id,
            'product_type_id' => $validated['product_type_id'],
            'product_category_id' => $validated['product_category_id'] ?? null,
            'base_unit_id' => $validated['base_unit_id'],
            'name' => $validated['name'],
            'code' => $this->generateProductCode($pharmacy->id, $validated['name']),
            'barcode' => $this->generateBarcode($pharmacy->id),
            'generic_name' => $validated['generic_name'] ?? null,
            'strength' => $validated['strength'] ?? null,
            'brand' => $validated['brand'] ?? null,
            'requires_expiry' => $request->boolean('requires_expiry', true),
            'requires_prescription' => $request->boolean('requires_prescription'),
            'is_active' => $request->boolean('is_active', true),
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()
            ->route('product-setup.index', ['tab' => 'products'])
            ->with('success', 'Product created successfully. Now configure package units and prices.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->guardPharmacy($product);

        $validated = $request->validate([
            'product_type_id' => [
                'required',
                Rule::exists('product_types', 'id')->where('pharmacy_id', $product->pharmacy_id),
            ],
            'product_category_id' => [
                'nullable',
                Rule::exists('product_categories', 'id')->where('pharmacy_id', $product->pharmacy_id),
            ],
            'base_unit_id' => [
                'required',
                Rule::exists('units', 'id')->where('pharmacy_id', $product->pharmacy_id),
            ],
            'name' => ['required', 'string', 'max:180'],
            'generic_name' => ['nullable', 'string', 'max:180'],
            'strength' => ['nullable', 'string', 'max:80'],
            'brand' => ['nullable', 'string', 'max:120'],
            'requires_expiry' => ['nullable', 'boolean'],
            'requires_prescription' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
        ]);

        $product->update([
            'product_type_id' => $validated['product_type_id'],
            'product_category_id' => $validated['product_category_id'] ?? null,
            'base_unit_id' => $validated['base_unit_id'],
            'name' => $validated['name'],
            'code' => $product->code ?: $this->generateProductCode($product->pharmacy_id, $validated['name']),
            'barcode' => $product->barcode ?: $this->generateBarcode($product->pharmacy_id),
            'generic_name' => $validated['generic_name'] ?? null,
            'strength' => $validated['strength'] ?? null,
            'brand' => $validated['brand'] ?? null,
            'requires_expiry' => $request->boolean('requires_expiry'),
            'requires_prescription' => $request->boolean('requires_prescription'),
            'is_active' => $request->boolean('is_active'),
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()
            ->route('product-setup.index', ['tab' => 'products'])
            ->with('success', 'Product updated successfully.');
    }

    public function toggle(Product $product): RedirectResponse
    {
        $this->guardPharmacy($product);

        $product->update([
            'is_active' => ! $product->is_active,
        ]);

        return back()->with('success', 'Product status updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->guardPharmacy($product);

        if ($product->units()->exists() || $product->prices()->exists()) {
            return back()->with('error', 'This product already has setup records. Deactivate it instead of deleting.');
        }

        Product::query()
            ->whereKey($product->id)
            ->delete();

        return back()->with('success', 'Product deleted successfully.');
    }

public function export(Request $request): BinaryFileResponse|Response|RedirectResponse
{
    $format = (string) $request->input('format', 'sample');

    $pharmacy = Pharmacy::query()->firstOrFail();

    if ($format === 'sample') {
        return Excel::download(
            new ProductImportTemplateExport($pharmacy),
            'product_import_template.xlsx'
        );
    }

    if ($format !== 'pdf') {
        return back()->with('error', 'Excel product list export will be added later. PDF export is ready.');
    }

    $products = Product::query()
        ->with([
            'productType',
            'category',
            'baseUnit',
            'units.unit',
            'units.prices',
        ])
        ->where('pharmacy_id', $pharmacy->id)
        ->where('is_active', true)
        ->orderBy(
            ProductType::query()
                ->select('name')
                ->whereColumn('product_types.id', 'products.product_type_id')
                ->limit(1)
        )
        ->orderBy(
            ProductCategory::query()
                ->select('name')
                ->whereColumn('product_categories.id', 'products.product_category_id')
                ->limit(1)
        )
        ->orderBy('name')
        ->get();

    $groupedProducts = $products
        ->groupBy(fn (Product $product) => $product->productType?->name ?: 'Uncategorized Type')
        ->map(function ($typeProducts) {
            return $typeProducts->groupBy(
                fn (Product $product) => $product->category?->name ?: 'Uncategorized Category'
            );
        });

    $pdf = Pdf::loadView('product-setup.exports.price-list-pdf', [
        'pharmacy' => $pharmacy,
        'groupedProducts' => $groupedProducts,
        'generatedAt' => now(),
    ])->setPaper('a4', 'portrait');

    return new Response($pdf->output(), 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="product-price-list.pdf"',
    ]);
}
    public function import(Request $request): RedirectResponse
{
    $validated = $request->validate([
        'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
    ]);

    $pharmacy = Pharmacy::query()->firstOrFail();

    $import = new ProductSetupImport($pharmacy);

    try {
        Excel::import($import, $validated['file']);

        $summary = $import->summary();

        return redirect()
            ->route('product-setup.index', ['tab' => 'products'])
            ->with('success', sprintf(
                'Import completed. Products: %d created, %d updated. Types: %d created, %d updated. Categories: %d created, %d updated. Units: %d created, %d updated. Package units: %d created, %d updated. Prices: %d created, %d updated.',
                $summary['products_created'],
                $summary['products_updated'],
                $summary['types_created'],
                $summary['types_updated'],
                $summary['categories_created'],
                $summary['categories_updated'],
                $summary['units_created'],
                $summary['units_updated'],
                $summary['product_units_created'],
                $summary['product_units_updated'],
                $summary['prices_created'],
                $summary['prices_updated'],
            ));
    } catch (ValidationException $exception) {
        throw $exception;
    } catch (\Throwable $exception) {
        return back()->with('error', 'Import failed: ' . $exception->getMessage());
    }
}

    private function generateProductCode(int $pharmacyId, string $name): string
    {
        $base = strtoupper(Str::slug($name, '_'));
        $base = preg_replace('/[^A-Z0-9_]/', '', $base) ?: 'PRODUCT';
        $base = substr($base, 0, 45);

        $code = $base;
        $counter = 1;

        while (Product::query()
            ->where('pharmacy_id', $pharmacyId)
            ->where('code', $code)
            ->exists()) {
            $code = $base . '_' . str_pad((string) $counter, 2, '0', STR_PAD_LEFT);
            $counter++;
        }

        return $code;
    }

    private function generateBarcode(int $pharmacyId): string
    {
        do {
            $barcode = 'PRD' .
                str_pad((string) $pharmacyId, 3, '0', STR_PAD_LEFT) .
                now()->format('ymd') .
                random_int(100000, 999999);
        } while (Product::query()
            ->where('pharmacy_id', $pharmacyId)
            ->where('barcode', $barcode)
            ->exists());

        return $barcode;
    }

    private function guardPharmacy(Product $product): void
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        if ((int) $product->pharmacy_id !== (int) $pharmacy->id) {
            abort(403);
        }
    }
}