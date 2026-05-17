<?php

namespace App\Services\Intelligence;

use App\Models\Branch;
use App\Models\IntelligenceAlert;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductIntelligenceSnapshot;
use App\Models\PublicProductSearchLog;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PharmacyIntelligenceService
{
    public function generateForPharmacy(
        int $pharmacyId,
        ?int $branchId = null,
        ?Carbon $periodStart = null,
        ?Carbon $periodEnd = null
    ): int {
        $periodStart ??= now()->startOfMonth();
        $periodEnd ??= now()->endOfMonth();

        /** @var \Illuminate\Database\Eloquent\Collection<int, Product> $products */
        $products = Product::query()
            ->where('pharmacy_id', $pharmacyId)
            ->where('is_active', true)
            ->get();

        $generated = 0;

        foreach ($products as $product) {
            if (! $product instanceof Product) {
                $product = Product::hydrate([(array) $product])->first();
            }

            $snapshot = $this->buildProductSnapshot(
                product: $product,
                pharmacyId: $pharmacyId,
                branchId: $branchId,
                periodStart: $periodStart,
                periodEnd: $periodEnd
            );

            ProductIntelligenceSnapshot::query()->updateOrCreate(
                [
                    'pharmacy_id' => $pharmacyId,
                    'branch_id' => $branchId,
                    'product_id' => $product->id,
                    'period_type' => 'monthly',
                    'period_start' => $periodStart->toDateString(),
                    'period_end' => $periodEnd->toDateString(),
                ],
                $snapshot
            );

            $this->createAlerts($snapshot);

            $generated++;
        }

        return $generated;
    }

    private function buildProductSnapshot(
        Product $product,
        int $pharmacyId,
        ?int $branchId,
        Carbon $periodStart,
        Carbon $periodEnd
    ): array {
        $sales = $this->salesStats($product->id, $pharmacyId, $branchId, $periodStart, $periodEnd);
        $stock = $this->stockStats($product->id, $pharmacyId, $branchId);
        $searches = $this->searchStats($product->id, $pharmacyId, $branchId, $periodStart, $periodEnd);

        $days = max(1, $periodStart->diffInDays($periodEnd) + 1);
        $avgDailySales = round(((int) $sales['sales_base_units']) / $days, 2);

        $stockCoverDays = $avgDailySales > 0
            ? round(((int) $stock['current_stock_base_units']) / $avgDailySales, 2)
            : null;

        $profitMargin = (float) $sales['revenue'] > 0
            ? round(((float) $sales['gross_profit'] / (float) $sales['revenue']) * 100, 2)
            : 0;

        $scores = $this->scoreProduct(
            salesBaseUnits: (int) $sales['sales_base_units'],
            revenue: (float) $sales['revenue'],
            grossProfit: (float) $sales['gross_profit'],
            publicSearches: (int) $searches['public_searches'],
            missedSearches: (int) $searches['missed_searches'],
            currentStock: (int) $stock['current_stock_base_units'],
            stockCoverDays: $stockCoverDays,
            nearExpiry: (int) $stock['near_expiry_base_units'],
            expired: (int) $stock['expired_base_units']
        );

        $recommendation = $this->recommendationText(
            productName: $product->name,
            scores: $scores,
            salesBaseUnits: (int) $sales['sales_base_units'],
            publicSearches: (int) $searches['public_searches'],
            missedSearches: (int) $searches['missed_searches'],
            currentStock: (int) $stock['current_stock_base_units'],
            stockCoverDays: $stockCoverDays,
            nearExpiry: (int) $stock['near_expiry_base_units'],
            expired: (int) $stock['expired_base_units'],
            profitMargin: $profitMargin
        );

        return [
            'pharmacy_id' => $pharmacyId,
            'branch_id' => $branchId,
            'product_id' => $product->id,
            'period_type' => 'monthly',
            'period_start' => $periodStart->toDateString(),
            'period_end' => $periodEnd->toDateString(),

            'sales_count' => $sales['sales_count'],
            'sales_base_units' => $sales['sales_base_units'],
            'revenue' => $sales['revenue'],
            'gross_profit' => $sales['gross_profit'],
            'profit_margin' => $profitMargin,

            'public_searches' => $searches['public_searches'],
            'missed_searches' => $searches['missed_searches'],

            'current_stock_base_units' => $stock['current_stock_base_units'],
            'avg_daily_sales_base_units' => $avgDailySales,
            'stock_cover_days' => $stockCoverDays,

            'near_expiry_base_units' => $stock['near_expiry_base_units'],
            'expired_base_units' => $stock['expired_base_units'],

            'sales_score' => $scores['sales_score'],
            'search_score' => $scores['search_score'],
            'profit_score' => $scores['profit_score'],
            'stock_risk_score' => $scores['stock_risk_score'],
            'expiry_risk_score' => $scores['expiry_risk_score'],
            'priority_score' => $scores['priority_score'],

            'recommendation_type' => $recommendation['type'],
            'recommendation_text' => $recommendation['text'],

            'meta' => [
                'generated_at' => now()->toDateTimeString(),
            ],
        ];
    }

    private function salesStats(int $productId, int $pharmacyId, ?int $branchId, Carbon $start, Carbon $end): array
    {
        $query = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id', 'inner', false)
            ->where('sale_items.pharmacy_id', $pharmacyId)
            ->where('sale_items.product_id', $productId)
            ->where('sales.status', 'completed')
            ->whereBetween('sales.sold_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()]);

        if ($branchId) {
            $query->where('sale_items.branch_id', $branchId);
        }

        $row = $query->selectRaw('
            COUNT(DISTINCT sale_items.sale_id) as sales_count,
            COALESCE(SUM(sale_items.total_base_units), 0) as sales_base_units,
            COALESCE(SUM(sale_items.line_total), 0) as revenue,
            COALESCE(SUM(sale_items.profit_amount), 0) as gross_profit
        ')->first();

        return [
            'sales_count' => (int) ($row->sales_count ?? 0),
            'sales_base_units' => (int) ($row->sales_base_units ?? 0),
            'revenue' => (float) ($row->revenue ?? 0),
            'gross_profit' => (float) ($row->gross_profit ?? 0),
        ];
    }

    private function stockStats(int $productId, int $pharmacyId, ?int $branchId): array
    {
        $query = Inventory::query()
            ->where('pharmacy_id', $pharmacyId)
            ->where('product_id', $productId)
            ->where('is_active', true);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return [
            'current_stock_base_units' => (int) (clone $query)
                ->whereIn('status', ['available', 'blocked'])
                ->sum('available_quantity_base_units'),

            'near_expiry_base_units' => (int) (clone $query)
                ->whereDate('expiry_date', '>=', now())
                ->whereDate('expiry_date', '<=', now()->addDays(60))
                ->sum('available_quantity_base_units'),

            'expired_base_units' => (int) (clone $query)
                ->whereDate('expiry_date', '<', now())
                ->sum('available_quantity_base_units'),
        ];
    }

    private function searchStats(int $productId, int $pharmacyId, ?int $branchId, Carbon $start, Carbon $end): array
    {
        $query = PublicProductSearchLog::query()
            ->where('pharmacy_id', $pharmacyId)
            ->where('product_id', $productId)
            ->whereBetween('created_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()]);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return [
            'public_searches' => (int) (clone $query)->count(),
            'missed_searches' => (int) (clone $query)
                ->whereIn('result_status', ['not_found', 'out_of_stock', 'no_result'])
                ->count(),
        ];
    }

    private function scoreProduct(
        int $salesBaseUnits,
        float $revenue,
        float $grossProfit,
        int $publicSearches,
        int $missedSearches,
        int $currentStock,
        ?float $stockCoverDays,
        int $nearExpiry,
        int $expired
    ): array {
        $salesScore = min(30, (int) floor($salesBaseUnits / 10));
        $searchScore = min(20, ($publicSearches * 2) + ($missedSearches * 3));
        $profitScore = $revenue > 0 ? min(20, (int) floor(($grossProfit / max($revenue, 1)) * 100 / 2)) : 0;

        $stockRiskScore = match (true) {
            $currentStock <= 0 && ($salesBaseUnits > 0 || $publicSearches > 0) => 30,
            $stockCoverDays !== null && $stockCoverDays <= 3 => 25,
            $stockCoverDays !== null && $stockCoverDays <= 7 => 18,
            $stockCoverDays !== null && $stockCoverDays <= 14 => 10,
            default => 0,
        };

        $expiryRiskScore = match (true) {
            $expired > 0 => 30,
            $nearExpiry > 0 => 15,
            default => 0,
        };

        $priorityScore = max(0, min(100,
            $salesScore
            + $searchScore
            + $profitScore
            + $stockRiskScore
            - $expiryRiskScore
        ));

        return compact(
            'salesScore',
            'searchScore',
            'profitScore',
            'stockRiskScore',
            'expiryRiskScore',
            'priorityScore'
        ) + [
            'sales_score' => $salesScore,
            'search_score' => $searchScore,
            'profit_score' => $profitScore,
            'stock_risk_score' => $stockRiskScore,
            'expiry_risk_score' => $expiryRiskScore,
            'priority_score' => $priorityScore,
        ];
    }

    private function recommendationText(
        string $productName,
        array $scores,
        int $salesBaseUnits,
        int $publicSearches,
        int $missedSearches,
        int $currentStock,
        ?float $stockCoverDays,
        int $nearExpiry,
        int $expired,
        float $profitMargin
    ): array {
        if ($expired > 0) {
            return [
                'type' => 'expired_stock',
                'text' => "{$productName} has expired stock. Review immediately and remove it from sellable stock.",
            ];
        }

        if ($nearExpiry > 0 && $salesBaseUnits <= 0) {
            return [
                'type' => 'near_expiry_slow',
                'text' => "{$productName} has near-expiry stock but low sales movement. Consider transfer, promotion, or supplier return.",
            ];
        }

        if ($currentStock <= 0 && ($publicSearches > 0 || $salesBaseUnits > 0)) {
            return [
                'type' => 'missed_demand',
                'text' => "{$productName} is demanded but currently out of stock. Restock to avoid missing customers.",
            ];
        }

        if ($stockCoverDays !== null && $stockCoverDays <= 7) {
            return [
                'type' => 'restock',
                'text' => "{$productName} is moving fast and current stock may last about {$stockCoverDays} days. Consider restocking soon.",
            ];
        }

        if ($salesBaseUnits <= 0 && $currentStock > 0) {
            return [
                'type' => 'slow_moving',
                'text' => "{$productName} has stock but no sales in this period. Avoid overbuying until demand improves.",
            ];
        }

        if ($profitMargin > 0 && $profitMargin < 10) {
            return [
                'type' => 'price_review',
                'text' => "{$productName} is selling with low profit margin. Review supplier cost or selling price.",
            ];
        }

        return [
            'type' => 'normal',
            'text' => "{$productName} is stable. Continue monitoring sales, stock, and customer demand.",
        ];
    }

    private function createAlerts(array $snapshot): void
    {
        $type = $snapshot['recommendation_type'];

        if (! in_array($type, ['restock', 'missed_demand', 'expired_stock', 'near_expiry_slow', 'price_review'], true)) {
            return;
        }

        $severity = match ($type) {
            'expired_stock', 'missed_demand' => 'critical',
            'restock', 'near_expiry_slow' => 'warning',
            default => 'info',
        };

        IntelligenceAlert::query()->updateOrCreate(
            [
                'pharmacy_id' => $snapshot['pharmacy_id'],
                'branch_id' => $snapshot['branch_id'],
                'product_id' => $snapshot['product_id'],
                'alert_type' => $type,
                'status' => 'open',
            ],
            [
                'severity' => $severity,
                'title' => str($type)->replace('_', ' ')->title()->toString(),
                'message' => $snapshot['recommendation_text'],
                'meta' => [
                    'priority_score' => $snapshot['priority_score'],
                    'period_start' => $snapshot['period_start'],
                    'period_end' => $snapshot['period_end'],
                ],
            ]
        );
    }
}