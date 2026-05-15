<?php

namespace App\Services;

use App\Models\PublicProductSearchLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PublicSearchAnalyticsService
{
    public function summary(?int $branchId = null, string $period = 'today'): array
    {
        [$from, $to] = $this->periodRange($period);

        $base = PublicProductSearchLog::query()
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->whereBetween('created_at', [$from, $to]);

        $totalSearches = (clone $base)->count();

        $availableCount = (clone $base)
            ->where('result_status', 'available')
            ->count();

        $notAvailableCount = (clone $base)
            ->where('result_status', 'not_available')
            ->count();

        $noResultsCount = (clone $base)
            ->where('result_status', 'no_results')
            ->count();

        $uniqueQueries = (clone $base)
            ->whereNotNull('query')
            ->distinct('query')
            ->count('query');

        return [
            'period' => $period,
            'from' => $from->toDateTimeString(),
            'to' => $to->toDateTimeString(),
            'cards' => [
                'total_searches' => $totalSearches,
                'available' => $availableCount,
                'not_available' => $notAvailableCount,
                'no_results' => $noResultsCount,
                'unique_queries' => $uniqueQueries,
            ],
            'top_products' => $this->topProducts($branchId, $from, $to),
            'top_queries' => $this->topQueries($branchId, $from, $to),
            'not_available_products' => $this->notAvailableProducts($branchId, $from, $to),
            'daily_trend' => $this->dailyTrend($branchId, $from, $to),
            'recent_logs' => $this->recentLogs($branchId, $from, $to),
        ];
    }

    protected function topProducts(?int $branchId, Carbon $from, Carbon $to): array
    {
        return PublicProductSearchLog::query()
            ->select([
                'product_id',
                DB::raw('COUNT(*) as total'),
            ])
            ->with('product:id,name,generic_name,strength,brand')
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->whereNotNull('product_id')
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('product_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'product_id' => $row->product_id,
                'product_name' => $row->product?->name ?? 'Unknown Product',
                'generic_name' => $row->product?->generic_name,
                'strength' => $row->product?->strength,
                'total' => (int) $row->total,
            ])
            ->values()
            ->all();
    }

    protected function topQueries(?int $branchId, Carbon $from, Carbon $to): array
    {
        return PublicProductSearchLog::query()
            ->select([
                'query',
                DB::raw('COUNT(*) as total'),
                DB::raw('MAX(result_status) as latest_status'),
            ])
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->whereNotNull('query')
            ->where('query', '!=', '')
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('query')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'query' => $row->query,
                'total' => (int) $row->total,
                'latest_status' => $row->latest_status,
            ])
            ->values()
            ->all();
    }

    protected function notAvailableProducts(?int $branchId, Carbon $from, Carbon $to): array
    {
        return PublicProductSearchLog::query()
            ->select([
                'product_id',
                'query',
                DB::raw('COUNT(*) as total'),
            ])
            ->with('product:id,name,generic_name,strength')
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->where('result_status', 'not_available')
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('product_id', 'query')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'product_id' => $row->product_id,
                'product_name' => $row->product?->name ?? $row->query,
                'query' => $row->query,
                'total' => (int) $row->total,
            ])
            ->values()
            ->all();
    }

    protected function dailyTrend(?int $branchId, Carbon $from, Carbon $to): array
    {
        return PublicProductSearchLog::query()
            ->select([
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total'),
            ])
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->whereBetween('created_at', [$from, $to])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => [
                'date' => $row->date,
                'total' => (int) $row->total,
            ])
            ->values()
            ->all();
    }

    protected function recentLogs(?int $branchId, Carbon $from, Carbon $to): array
    {
        return PublicProductSearchLog::query()
            ->with([
                'branch:id,name',
                'product:id,name,generic_name,strength',
            ])
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->whereBetween('created_at', [$from, $to])
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn ($log) => [
                'id' => $log->id,
                'branch' => $log->branch?->name,
                'product' => $log->product?->name,
                'query' => $log->query,
                'result_status' => $log->result_status,
                'results_count' => $log->results_count,
                'created_at' => $log->created_at?->toDateTimeString(),
            ])
            ->values()
            ->all();
    }

    protected function periodRange(string $period): array
    {
        return match ($period) {
            '7days' => [now()->subDays(6)->startOfDay(), now()->endOfDay()],
            '30days' => [now()->subDays(29)->startOfDay(), now()->endOfDay()],
            'month' => [now()->startOfMonth(), now()->endOfDay()],
            default => [now()->startOfDay(), now()->endOfDay()],
        };
    }
}