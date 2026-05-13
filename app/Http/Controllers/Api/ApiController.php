<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ActivityLogResource;
use App\Http\Resources\Api\BranchResource;
use App\Http\Resources\Api\DailyClosingResource;
use App\Http\Resources\Api\ExpenseCategoryResource;
use App\Http\Resources\Api\ExpenseResource;
use App\Http\Resources\Api\InventoryAlertResource;
use App\Http\Resources\Api\InventoryMovementResource;
use App\Http\Resources\Api\InventoryResource;
use App\Http\Resources\Api\PharmacyResource;
use App\Http\Resources\Api\PharmacySettingResource;
use App\Http\Resources\Api\ProductCategoryResource;
use App\Http\Resources\Api\ProductPriceResource;
use App\Http\Resources\Api\ProductResource;
use App\Http\Resources\Api\ProductTypeResource;
use App\Http\Resources\Api\ProductUnitResource;
use App\Http\Resources\Api\PurchaseItemResource;
use App\Http\Resources\Api\PurchaseResource;
use App\Http\Resources\Api\RoleResource;
use App\Http\Resources\Api\SaleItemResource;
use App\Http\Resources\Api\SaleResource;
use App\Http\Resources\Api\SalesReturnItemResource;
use App\Http\Resources\Api\SalesReturnResource;
use App\Http\Resources\Api\StockAdjustmentItemResource;
use App\Http\Resources\Api\StockAdjustmentResource;
use App\Http\Resources\Api\StockTransferItemResource;
use App\Http\Resources\Api\StockTransferResource;
use App\Http\Resources\Api\SupplierResource;
use App\Http\Resources\Api\UnitResource;
use App\Http\Resources\Api\UserResource;
use App\Models\Branch;
use App\Models\DailyClosing;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Inventory;
use App\Models\InventoryAlert;
use App\Models\InventoryMovement;
use App\Models\Pharmacy;
use App\Models\PharmacySetting;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductPrice;
use App\Models\ProductType;
use App\Models\ProductUnit;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response as LaravelResponse;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Routing\Controller as RoutingController;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware as ControllerMiddleware;
use Illuminate\Routing\MiddlewareNameResolver;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class ApiController extends Controller
{
    /**
     * @var array<class-string<Model>, class-string>
     */
    protected array $resourceMap = [
        Activity::class => ActivityLogResource::class,
        Branch::class => BranchResource::class,
        DailyClosing::class => DailyClosingResource::class,
        Expense::class => ExpenseResource::class,
        ExpenseCategory::class => ExpenseCategoryResource::class,
        Inventory::class => InventoryResource::class,
        InventoryAlert::class => InventoryAlertResource::class,
        InventoryMovement::class => InventoryMovementResource::class,
        Pharmacy::class => PharmacyResource::class,
        PharmacySetting::class => PharmacySettingResource::class,
        Product::class => ProductResource::class,
        ProductCategory::class => ProductCategoryResource::class,
        ProductPrice::class => ProductPriceResource::class,
        ProductType::class => ProductTypeResource::class,
        ProductUnit::class => ProductUnitResource::class,
        Purchase::class => PurchaseResource::class,
        PurchaseItem::class => PurchaseItemResource::class,
        Role::class => RoleResource::class,
        Sale::class => SaleResource::class,
        SaleItem::class => SaleItemResource::class,
        SalesReturn::class => SalesReturnResource::class,
        SalesReturnItem::class => SalesReturnItemResource::class,
        StockAdjustment::class => StockAdjustmentResource::class,
        StockAdjustmentItem::class => StockAdjustmentItemResource::class,
        StockTransfer::class => StockTransferResource::class,
        StockTransferItem::class => StockTransferItemResource::class,
        Supplier::class => SupplierResource::class,
        Unit::class => UnitResource::class,
        User::class => UserResource::class,
    ];

    protected function success(mixed $data = [], ?string $message = null, int $status = 200): JsonResponse
    {
        if ($data instanceof AbstractPaginator) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $this->normalize($data->getCollection()),
                'meta' => $this->paginationMeta($data),
            ], $status);
        }

        $payload = [
            'success' => true,
            'message' => $message,
            'data' => $this->normalize($data),
        ];

        if ($message === null) {
            unset($payload['message']);
        }

        return response()->json($payload, $status);
    }

    protected function error(string $message, int $status = 400, ?array $errors = null): JsonResponse
    {
        return response()->json(array_filter([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], fn ($value) => $value !== null), $status);
    }

    protected function callWeb(string $controllerClass, string $method): mixed
    {
        $request = request();
        $controller = app($controllerClass);

        $response = app(Pipeline::class)
            ->send($request)
            ->through($this->resolveMiddleware($this->controllerMiddleware($controller, $controllerClass, $method)))
            ->then(fn () => app()->call([$controller, $method], $request->route()?->parameters() ?? []));

        return $this->apiResponseFrom($response);
    }

    protected function apiResponseFrom(mixed $response): mixed
    {
        $request = request();

        if ($response instanceof Responsable) {
            $response = $response->toResponse($request);
        }

        if ($response instanceof BinaryFileResponse || $response instanceof StreamedResponse) {
            return $response;
        }

        if ($response instanceof JsonResponse) {
            return $this->normalizeJsonResponse($response);
        }

        if ($response instanceof RedirectResponse) {
            return $this->redirectToJson($request, $response);
        }

        if ($response instanceof ViewContract) {
            return $this->success($response->getData());
        }

        if ($response instanceof LaravelResponse) {
            return $response;
        }

        if ($response instanceof Response) {
            return $response;
        }

        return $this->success($response);
    }

    protected function normalize(mixed $value): mixed
    {
        if ($value instanceof LengthAwarePaginator) {
            return [
                'items' => $this->normalize($value->getCollection()),
                'meta' => $this->paginationMeta($value),
            ];
        }

        if ($value instanceof AbstractPaginator) {
            return [
                'items' => $this->normalize($value->getCollection()),
                'meta' => $this->paginationMeta($value),
            ];
        }

        if ($value instanceof Collection) {
            return $value->map(fn ($item) => $this->normalize($item))->values();
        }

        if ($value instanceof Model) {
            return $this->resourceFor($value);
        }

        if ($value instanceof JsonResource) {
            return $value->resolve(request());
        }

        if ($value instanceof Arrayable) {
            return $this->normalize($value->toArray());
        }

        if (is_array($value)) {
            $normalized = [];

            foreach ($value as $key => $item) {
                if ($this->shouldStripKey((string) $key)) {
                    continue;
                }

                $normalized[$key] = $this->normalize($item);
            }

            return $normalized;
        }

        return $value;
    }

    protected function resourceFor(Model $model): array
    {
        foreach ($this->resourceMap as $class => $resourceClass) {
            if ($model instanceof $class) {
                return (new $resourceClass($model))->resolve(request());
            }
        }

        return $model->toArray();
    }

    private function normalizeJsonResponse(JsonResponse $response): JsonResponse
    {
        $status = $response->getStatusCode();
        $payload = $response->getData(true);
        $success = (bool) ($payload['success'] ?? $payload['ok'] ?? ($status < 400));
        $message = $payload['message'] ?? null;

        unset($payload['success'], $payload['ok'], $payload['message']);

        if (array_key_exists('data', $payload) && count($payload) === 1) {
            $data = $payload['data'];
        } else {
            $data = $payload;
        }

        $body = [
            'success' => $success,
            'message' => $message,
            'data' => $this->normalize($data),
        ];

        if ($message === null) {
            unset($body['message']);
        }

        if (! $success && empty($body['data'])) {
            unset($body['data']);
        }

        return response()->json($body, $status);
    }

    private function redirectToJson(Request $request, RedirectResponse $response): JsonResponse
    {
        $message = null;
        $errors = null;

        if ($request->hasSession()) {
            $session = $request->session();
            $message = $session->get('success')
                ?? $session->get('status')
                ?? $session->get('error');

            $errors = $this->formatErrors($session->get('errors'));
        }

        $isError = filled($errors) || ($request->hasSession() && $request->session()->has('error'));

        return response()->json(array_filter([
            'success' => ! $isError,
            'message' => $message ?? ($isError ? 'The request could not be completed.' : 'Request completed successfully.'),
            'errors' => $errors,
        ], fn ($value) => $value !== null), $isError ? 422 : 200);
    }

    private function paginationMeta(AbstractPaginator $paginator): array
    {
        return [
            'current_page' => method_exists($paginator, 'currentPage') ? $paginator->currentPage() : null,
            'last_page' => method_exists($paginator, 'lastPage') ? $paginator->lastPage() : null,
            'per_page' => method_exists($paginator, 'perPage') ? $paginator->perPage() : null,
            'total' => method_exists($paginator, 'total') ? $paginator->total() : null,
        ];
    }

    private function shouldStripKey(string $key): bool
    {
        return in_array($key, [
            'html',
            'modal_html',
            'modalHtml',
            'view',
            'delete_url',
            'receipt_url',
            '_token',
        ], true)
            || Str::endsWith($key, ['_html']);
    }

    private function controllerMiddleware(object $controller, string $controllerClass, string $method): array
    {
        $middleware = [];

        if ($controller instanceof RoutingController) {
            foreach ($controller->getMiddleware() as $definition) {
                if ($this->middlewareApplies($definition['options']['only'] ?? null, $definition['options']['except'] ?? null, $method)) {
                    $middleware[] = $definition['middleware'];
                }
            }
        }

        if (is_subclass_of($controllerClass, HasMiddleware::class)) {
            foreach ($controllerClass::middleware() as $definition) {
                if ($definition instanceof ControllerMiddleware) {
                    if ($this->middlewareApplies($definition->only, $definition->except, $method)) {
                        $middleware[] = $definition->middleware;
                    }

                    continue;
                }

                $middleware[] = $definition;
            }
        }

        return $middleware;
    }

    private function resolveMiddleware(array $middleware): array
    {
        $router = app('router');
        $resolved = [];

        foreach ($middleware as $definition) {
            if ($definition instanceof \Closure || is_object($definition)) {
                $resolved[] = $definition;

                continue;
            }

            foreach ((array) MiddlewareNameResolver::resolve($definition, $router->getMiddleware(), $router->getMiddlewareGroups()) as $item) {
                $resolved[] = $item;
            }
        }

        return $resolved;
    }

    private function middlewareApplies(?array $only, ?array $except, string $method): bool
    {
        if ($only !== null && ! in_array($method, $only, true)) {
            return false;
        }

        if ($except !== null && in_array($method, $except, true)) {
            return false;
        }

        return true;
    }

    private function formatErrors(mixed $errors): ?array
    {
        if (! $errors instanceof ViewErrorBag) {
            return null;
        }

        $formatted = [];

        foreach ($errors->getBags() as $name => $bag) {
            $formatted[$name] = $bag->toArray();
        }

        return $formatted ?: null;
    }
}
