<?php

namespace App\BigData;

use App\Models\Borrowing;
use App\Models\BorrowingDetail;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

/**
 * SparkAnalytics — Simulates Apache Spark Data Analytics Engine
 *
 * In a real Spark setup, this would run as a distributed Spark job
 * processing large datasets across a cluster. Here we simulate
 * Spark-style analytics operations using Laravel's query builder.
 *
 * Features:
 * - Trend analysis (time-series borrowing patterns)
 * - Stock forecasting (moving average)
 * - Utilization rate calculation
 * - Category distribution analysis
 */
class SparkAnalytics
{
    /**
     * Run all analytics and return comprehensive results
     */
    public static function runFullAnalysis(): array
    {
        $startTime = microtime(true);

        $results = [
            'borrowing_trends' => self::analyzeBorrowingTrends(),
            'stock_forecast' => self::forecastStock(),
            'utilization_rates' => self::calculateUtilizationRates(),
            'category_distribution' => self::analyzeCategoryDistribution(),
            'top_borrowed_items' => self::getTopBorrowedItems(),
            'overdue_analysis' => self::analyzeOverduePatterns(),
            'monthly_summary' => self::getMonthlySummary(),
        ];

        $elapsed = (microtime(true) - $startTime) * 1000;

        $results['_metadata'] = [
            'engine' => 'spark-analytics-simulator',
            'processing_time_ms' => round($elapsed, 2),
            'generated_at' => now()->toISOString(),
            'data_points_analyzed' => self::countDataPoints(),
        ];

        // Log the analysis to Kafka
        KafkaSimulator::produce(
            KafkaSimulator::TOPIC_ANALYTICS,
            'SPARK_ANALYSIS_COMPLETE',
            ['processing_time_ms' => round($elapsed, 2), 'modules' => array_keys($results)]
        );

        return $results;
    }

    /**
     * Analyze borrowing trends over the last 12 months
     */
    public static function analyzeBorrowingTrends(): array
    {
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthKey = $date->format('Y-m');
            $monthLabel = $date->format('M Y');

            $count = Borrowing::whereYear('tanggal_pinjam', $date->year)
                ->whereMonth('tanggal_pinjam', $date->month)
                ->count();

            $returned = Borrowing::whereYear('tanggal_pinjam', $date->year)
                ->whereMonth('tanggal_pinjam', $date->month)
                ->where('status', 'dikembalikan')
                ->count();

            $months[] = [
                'month' => $monthKey,
                'label' => $monthLabel,
                'total_borrowings' => $count,
                'returned' => $returned,
                'active' => $count - $returned,
            ];
        }

        // Calculate trend direction
        $recentAvg = collect(array_slice($months, -3))->avg('total_borrowings');
        $olderAvg = collect(array_slice($months, 0, 3))->avg('total_borrowings');
        $trend = $recentAvg > $olderAvg ? 'increasing' : ($recentAvg < $olderAvg ? 'decreasing' : 'stable');

        return [
            'data' => $months,
            'trend' => $trend,
            'growth_rate' => $olderAvg > 0 ? round((($recentAvg - $olderAvg) / $olderAvg) * 100, 1) : 0,
        ];
    }

    /**
     * Forecast stock levels using simple moving average
     */
    public static function forecastStock(): array
    {
        $products = Product::with('category')->get();
        $forecasts = [];

        foreach ($products as $product) {
            // Calculate monthly consumption from borrowing details
            $isSqlite = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'sqlite';
            $monthExpr = $isSqlite ? "strftime('%m', created_at)" : "EXTRACT(MONTH FROM created_at)";

            $monthlyUsage = BorrowingDetail::where('product_id', $product->id)
                ->whereHas('borrowing', function ($q) {
                    $q->where('created_at', '>=', now()->subMonths(6));
                })
                ->selectRaw("{$monthExpr} as month, SUM(qty) as total_qty")
                ->groupByRaw($monthExpr)
                ->pluck('total_qty')
                ->toArray();

            // Simple moving average (if no data, use 0)
            $avgMonthlyUsage = count($monthlyUsage) > 0
                ? array_sum($monthlyUsage) / count($monthlyUsage)
                : 0;

            // Days until stockout
            $daysUntilStockout = $avgMonthlyUsage > 0
                ? round(($product->stok / ($avgMonthlyUsage / 30)), 0)
                : 999;

            $riskLevel = match (true) {
                $product->stok <= 0 => 'critical',
                $product->stok <= $product->min_stok => 'high',
                $daysUntilStockout <= 30 => 'medium',
                default => 'low',
            };

            $forecasts[] = [
                'product_id' => $product->id,
                'nama_barang' => $product->nama_barang,
                'category' => $product->category->name ?? 'N/A',
                'current_stock' => $product->stok,
                'min_stock' => $product->min_stok,
                'avg_monthly_usage' => round($avgMonthlyUsage, 1),
                'days_until_stockout' => min($daysUntilStockout, 999),
                'risk_level' => $riskLevel,
                'recommendation' => self::getRecommendation($riskLevel, $product),
            ];
        }

        // Sort by risk level (critical first)
        $riskOrder = ['critical' => 0, 'high' => 1, 'medium' => 2, 'low' => 3];
        usort($forecasts, fn($a, $b) => ($riskOrder[$a['risk_level']] ?? 4) <=> ($riskOrder[$b['risk_level']] ?? 4));

        return $forecasts;
    }

    /**
     * Calculate utilization rate for each product
     */
    public static function calculateUtilizationRates(): array
    {
        $products = Product::all();
        $rates = [];

        foreach ($products as $product) {
            $totalBorrowed = BorrowingDetail::where('product_id', $product->id)
                ->sum('qty');

            $activeBorrowings = BorrowingDetail::where('product_id', $product->id)
                ->whereHas('borrowing', fn($q) => $q->where('status', 'dipinjam'))
                ->sum('qty');

            $utilizationRate = $product->stok > 0
                ? round(($activeBorrowings / $product->stok) * 100, 1)
                : 0;

            $rates[] = [
                'product_id' => $product->id,
                'nama_barang' => $product->nama_barang,
                'total_stock' => $product->stok,
                'currently_borrowed' => $activeBorrowings,
                'available' => max(0, $product->stok - $activeBorrowings),
                'utilization_rate' => min(100, $utilizationRate),
                'total_times_borrowed' => $totalBorrowed,
                'status' => $utilizationRate >= 80 ? 'high_demand' : ($utilizationRate >= 40 ? 'moderate' : 'low_demand'),
            ];
        }

        usort($rates, fn($a, $b) => $b['utilization_rate'] <=> $a['utilization_rate']);

        return $rates;
    }

    /**
     * Analyze category distribution
     */
    public static function analyzeCategoryDistribution(): array
    {
        $categories = Category::withCount('products')
            ->with(['products' => function ($q) {
                $q->select('id', 'category_id', 'stok', 'kondisi');
            }])
            ->get();

        $distribution = [];
        $totalProducts = Product::count();

        foreach ($categories as $category) {
            $totalStock = $category->products->sum('stok');
            $goodCondition = $category->products->where('kondisi', 'baik')->count();
            $damaged = $category->products->whereIn('kondisi', ['rusak_ringan', 'rusak_berat'])->count();

            $distribution[] = [
                'category_id' => $category->id,
                'name' => $category->name,
                'product_count' => $category->products_count,
                'percentage' => $totalProducts > 0 ? round(($category->products_count / $totalProducts) * 100, 1) : 0,
                'total_stock' => $totalStock,
                'good_condition' => $goodCondition,
                'damaged' => $damaged,
            ];
        }

        return $distribution;
    }

    /**
     * Get top borrowed items
     */
    public static function getTopBorrowedItems(int $limit = 10): array
    {
        return BorrowingDetail::select('product_id')
            ->selectRaw('SUM(qty) as total_borrowed')
            ->selectRaw('COUNT(*) as borrow_count')
            ->with('product:id,nama_barang,kode_barang,stok')
            ->groupBy('product_id')
            ->orderByDesc('total_borrowed')
            ->limit($limit)
            ->get()
            ->map(fn($item) => [
                'product_id' => $item->product_id,
                'nama_barang' => $item->product->nama_barang ?? 'N/A',
                'kode_barang' => $item->product->kode_barang ?? 'N/A',
                'total_borrowed' => $item->total_borrowed,
                'borrow_count' => $item->borrow_count,
                'current_stock' => $item->product->stok ?? 0,
            ])
            ->toArray();
    }

    /**
     * Analyze overdue patterns
     */
    public static function analyzeOverduePatterns(): array
    {
        $overdue = Borrowing::where('status', 'dipinjam')
            ->whereNotNull('tanggal_kembali')
            ->where('tanggal_kembali', '<', now())
            ->with('details.product')
            ->get();

        $totalActive = Borrowing::where('status', 'dipinjam')->count();
        $overdueCount = $overdue->count();

        return [
            'total_overdue' => $overdueCount,
            'total_active' => $totalActive,
            'overdue_rate' => $totalActive > 0 ? round(($overdueCount / $totalActive) * 100, 1) : 0,
            'avg_days_overdue' => $overdue->avg(fn($b) => now()->diffInDays($b->tanggal_kembali)),
            'items' => $overdue->map(fn($b) => [
                'borrower' => $b->borrower_name,
                'due_date' => $b->tanggal_kembali->format('Y-m-d'),
                'days_overdue' => now()->diffInDays($b->tanggal_kembali),
                'products' => $b->details->map(fn($d) => $d->product->nama_barang ?? 'N/A')->toArray(),
            ])->toArray(),
        ];
    }

    /**
     * Get monthly summary statistics
     */
    public static function getMonthlySummary(): array
    {
        $summary = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);

            $summary[] = [
                'month' => $date->format('M Y'),
                'new_products' => Product::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)->count(),
                'borrowings' => Borrowing::whereYear('tanggal_pinjam', $date->year)
                    ->whereMonth('tanggal_pinjam', $date->month)->count(),
                'returns' => Borrowing::where('status', 'dikembalikan')
                    ->whereYear('tanggal_dikembalikan', $date->year)
                    ->whereMonth('tanggal_dikembalikan', $date->month)->count(),
            ];
        }

        return $summary;
    }

    /**
     * Count total data points analyzed
     */
    private static function countDataPoints(): int
    {
        return Product::count()
            + Borrowing::count()
            + BorrowingDetail::count()
            + Category::count();
    }

    /**
     * Generate stock recommendation
     */
    private static function getRecommendation(string $riskLevel, Product $product): string
    {
        return match ($riskLevel) {
            'critical' => "SEGERA restock {$product->nama_barang}! Stok habis.",
            'high' => "Stok {$product->nama_barang} menipis ({$product->stok} tersisa). Segera pesan ulang.",
            'medium' => "Pantau stok {$product->nama_barang}. Pertimbangkan untuk restock dalam 30 hari.",
            default => "Stok {$product->nama_barang} aman.",
        };
    }
}
