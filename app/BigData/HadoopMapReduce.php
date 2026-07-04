<?php

namespace App\BigData;

use App\Models\BorrowingDetail;
use App\Models\Category;
use App\Models\Product;

/**
 * HadoopMapReduce — Simulates Apache Hadoop MapReduce Processing
 *
 * In a real Hadoop setup, data would be distributed across HDFS nodes and
 * processed in parallel using Map and Reduce phases. Here we simulate
 * this distributed computing pattern.
 *
 * Architecture:
 * [Input Data] --> [Split] --> [Map Phase] --> [Shuffle] --> [Reduce Phase] --> [Output]
 *
 * Use Cases:
 * - Calculate total asset value per location
 * - Aggregate product conditions per category
 * - Cross-reference borrowings with stock data
 */
class HadoopMapReduce
{
    /** Processing progress tracker */
    private static array $progress = [];

    /**
     * Run a full MapReduce job
     */
    public static function runJob(string $jobType): array
    {
        $startTime = microtime(true);

        self::$progress = [
            'job_type' => $jobType,
            'status' => 'RUNNING',
            'phase' => 'INITIALIZING',
            'progress' => 0,
        ];

        $result = match ($jobType) {
            'asset_by_location' => self::assetByLocation(),
            'condition_by_category' => self::conditionByCategory(),
            'borrowing_cross_reference' => self::borrowingCrossReference(),
            'stock_aggregation' => self::stockAggregation(),
            default => ['error' => 'Unknown job type'],
        };

        $elapsed = (microtime(true) - $startTime) * 1000;

        // Log to Kafka
        KafkaSimulator::produce(
            KafkaSimulator::TOPIC_ANALYTICS,
            'HADOOP_JOB_COMPLETE',
            [
                'job_type' => $jobType,
                'processing_time_ms' => round($elapsed, 2),
                'records_processed' => $result['_metadata']['records_processed'] ?? 0,
            ]
        );

        $result['_metadata'] = array_merge($result['_metadata'] ?? [], [
            'engine' => 'hadoop-mapreduce-simulator',
            'job_type' => $jobType,
            'processing_time_ms' => round($elapsed, 2),
            'generated_at' => now()->toISOString(),
            'phases' => ['SPLIT', 'MAP', 'SHUFFLE', 'REDUCE', 'OUTPUT'],
        ]);

        return $result;
    }

    /**
     * Job: Calculate total assets grouped by storage location
     *
     * MAP: Each product emits (location, stock_count)
     * REDUCE: Sum stock_count for each location
     */
    public static function assetByLocation(): array
    {
        self::updateProgress('SPLIT', 10);

        // SPLIT phase: Get all products
        $products = Product::all();
        $chunks = $products->chunk(5); // Simulate data splitting

        self::updateProgress('MAP', 30);

        // MAP phase: Emit (location, data) pairs
        $mapped = [];
        foreach ($chunks as $chunk) {
            foreach ($chunk as $product) {
                $location = $product->lokasi ?? 'Tidak Diketahui';
                $mapped[] = [
                    'key' => $location,
                    'value' => [
                        'count' => 1,
                        'stock' => $product->stok,
                        'product' => $product->nama_barang,
                        'kondisi' => $product->kondisi,
                    ],
                ];
            }
            usleep(50000); // Simulate processing time
        }

        self::updateProgress('SHUFFLE', 60);

        // SHUFFLE phase: Group by key (location)
        $shuffled = [];
        foreach ($mapped as $pair) {
            $shuffled[$pair['key']][] = $pair['value'];
        }

        self::updateProgress('REDUCE', 80);

        // REDUCE phase: Aggregate values for each location
        $reduced = [];
        foreach ($shuffled as $location => $values) {
            $reduced[] = [
                'lokasi' => $location,
                'total_jenis_barang' => count($values),
                'total_stok' => array_sum(array_column($values, 'stock')),
                'kondisi_baik' => count(array_filter($values, fn($v) => $v['kondisi'] === 'baik')),
                'kondisi_rusak' => count(array_filter($values, fn($v) => $v['kondisi'] !== 'baik')),
                'items' => array_column($values, 'product'),
            ];
        }

        self::updateProgress('OUTPUT', 100);

        // Sort by total stock desc
        usort($reduced, fn($a, $b) => $b['total_stok'] <=> $a['total_stok']);

        return [
            'data' => $reduced,
            '_metadata' => [
                'records_processed' => count($products),
                'locations_found' => count($reduced),
                'map_outputs' => count($mapped),
            ],
        ];
    }

    /**
     * Job: Aggregate product conditions by category
     *
     * MAP: Each product emits (category, condition)
     * REDUCE: Count conditions per category
     */
    public static function conditionByCategory(): array
    {
        self::updateProgress('SPLIT', 10);

        $products = Product::with('category')->get();

        self::updateProgress('MAP', 30);

        // MAP
        $mapped = [];
        foreach ($products as $product) {
            $mapped[] = [
                'key' => $product->category->name ?? 'Uncategorized',
                'value' => [
                    'kondisi' => $product->kondisi,
                    'stok' => $product->stok,
                    'is_low_stock' => $product->isLowStock(),
                ],
            ];
        }

        self::updateProgress('SHUFFLE', 60);

        // SHUFFLE
        $shuffled = [];
        foreach ($mapped as $pair) {
            $shuffled[$pair['key']][] = $pair['value'];
        }

        self::updateProgress('REDUCE', 80);

        // REDUCE
        $reduced = [];
        foreach ($shuffled as $category => $values) {
            $reduced[] = [
                'kategori' => $category,
                'total_produk' => count($values),
                'total_stok' => array_sum(array_column($values, 'stok')),
                'baik' => count(array_filter($values, fn($v) => $v['kondisi'] === 'baik')),
                'rusak_ringan' => count(array_filter($values, fn($v) => $v['kondisi'] === 'rusak_ringan')),
                'rusak_berat' => count(array_filter($values, fn($v) => $v['kondisi'] === 'rusak_berat')),
                'low_stock_count' => count(array_filter($values, fn($v) => $v['is_low_stock'])),
                'health_score' => self::calculateHealthScore($values),
            ];
        }

        self::updateProgress('OUTPUT', 100);

        usort($reduced, fn($a, $b) => $b['health_score'] <=> $a['health_score']);

        return [
            'data' => $reduced,
            '_metadata' => [
                'records_processed' => count($products),
                'categories_analyzed' => count($reduced),
            ],
        ];
    }

    /**
     * Job: Cross-reference borrowings with current stock levels
     *
     * MAP: Each borrowing detail emits (product_id, borrowing_info)
     * REDUCE: Aggregate borrowing impact per product
     */
    public static function borrowingCrossReference(): array
    {
        self::updateProgress('SPLIT', 10);

        $details = BorrowingDetail::with(['product', 'borrowing'])->get();

        self::updateProgress('MAP', 30);

        // MAP
        $mapped = [];
        foreach ($details as $detail) {
            $mapped[] = [
                'key' => $detail->product_id,
                'value' => [
                    'qty' => $detail->qty,
                    'status' => $detail->borrowing->status ?? 'unknown',
                    'borrower' => $detail->borrowing->borrower_name ?? 'N/A',
                    'date' => $detail->borrowing->tanggal_pinjam?->format('Y-m-d'),
                    'product_name' => $detail->product->nama_barang ?? 'N/A',
                    'current_stock' => $detail->product->stok ?? 0,
                ],
            ];
        }

        self::updateProgress('SHUFFLE', 60);

        // SHUFFLE
        $shuffled = [];
        foreach ($mapped as $pair) {
            $shuffled[$pair['key']][] = $pair['value'];
        }

        self::updateProgress('REDUCE', 80);

        // REDUCE
        $reduced = [];
        foreach ($shuffled as $productId => $values) {
            $firstValue = $values[0];
            $activeBorrows = array_filter($values, fn($v) => $v['status'] === 'dipinjam');
            $totalBorrowed = array_sum(array_column($activeBorrows, 'qty'));

            $reduced[] = [
                'product_id' => $productId,
                'nama_barang' => $firstValue['product_name'],
                'current_stock' => $firstValue['current_stock'],
                'currently_borrowed' => $totalBorrowed,
                'available_stock' => max(0, $firstValue['current_stock'] - $totalBorrowed),
                'total_borrow_history' => count($values),
                'active_borrowers' => array_unique(array_column($activeBorrows, 'borrower')),
                'stock_impact_percentage' => $firstValue['current_stock'] > 0
                    ? round(($totalBorrowed / $firstValue['current_stock']) * 100, 1) : 0,
            ];
        }

        self::updateProgress('OUTPUT', 100);

        usort($reduced, fn($a, $b) => $b['stock_impact_percentage'] <=> $a['stock_impact_percentage']);

        return [
            'data' => $reduced,
            '_metadata' => [
                'records_processed' => count($details),
                'products_analyzed' => count($reduced),
            ],
        ];
    }

    /**
     * Job: Comprehensive stock aggregation across all dimensions
     */
    public static function stockAggregation(): array
    {
        self::updateProgress('SPLIT', 10);

        $products = Product::with('category')->get();

        self::updateProgress('MAP', 40);

        $totalStock = $products->sum('stok');
        $totalProducts = $products->count();
        $lowStockProducts = $products->filter(fn($p) => $p->isLowStock());

        self::updateProgress('REDUCE', 70);

        $byCondition = $products->groupBy('kondisi')->map(fn($group) => [
            'count' => $group->count(),
            'total_stock' => $group->sum('stok'),
        ]);

        self::updateProgress('OUTPUT', 100);

        return [
            'data' => [
                'summary' => [
                    'total_products' => $totalProducts,
                    'total_stock_units' => $totalStock,
                    'avg_stock_per_product' => $totalProducts > 0 ? round($totalStock / $totalProducts, 1) : 0,
                    'low_stock_count' => $lowStockProducts->count(),
                    'low_stock_percentage' => $totalProducts > 0
                        ? round(($lowStockProducts->count() / $totalProducts) * 100, 1) : 0,
                ],
                'by_condition' => $byCondition->toArray(),
                'stock_distribution' => [
                    'out_of_stock' => $products->where('stok', 0)->count(),
                    'critical' => $products->where('stok', '>', 0)->filter(fn($p) => $p->stok <= $p->min_stok)->count(),
                    'normal' => $products->filter(fn($p) => $p->stok > $p->min_stok && $p->stok <= $p->min_stok * 3)->count(),
                    'excess' => $products->filter(fn($p) => $p->stok > $p->min_stok * 3)->count(),
                ],
            ],
            '_metadata' => [
                'records_processed' => $totalProducts,
            ],
        ];
    }

    /**
     * Get available job types
     */
    public static function getAvailableJobs(): array
    {
        return [
            [
                'id' => 'asset_by_location',
                'name' => 'Asset By Location',
                'description' => 'Menghitung total aset yang dikelompokkan berdasarkan lokasi penyimpanan',
                'icon' => '📍',
            ],
            [
                'id' => 'condition_by_category',
                'name' => 'Condition By Category',
                'description' => 'Menganalisis kondisi barang per kategori dengan health score',
                'icon' => '📊',
            ],
            [
                'id' => 'borrowing_cross_reference',
                'name' => 'Borrowing Cross Reference',
                'description' => 'Cross-reference peminjaman dengan stok untuk analisis dampak',
                'icon' => '🔗',
            ],
            [
                'id' => 'stock_aggregation',
                'name' => 'Stock Aggregation',
                'description' => 'Agregasi stok komprehensif dari semua dimensi data',
                'icon' => '📦',
            ],
        ];
    }

    /**
     * Calculate health score for a category (0-100)
     */
    private static function calculateHealthScore(array $values): float
    {
        $total = count($values);
        if ($total === 0) return 0;

        $goodCount = count(array_filter($values, fn($v) => $v['kondisi'] === 'baik'));
        $lowStockCount = count(array_filter($values, fn($v) => $v['is_low_stock']));

        $conditionScore = ($goodCount / $total) * 60; // 60% weight for condition
        $stockScore = ((($total - $lowStockCount) / $total)) * 40; // 40% weight for stock

        return round($conditionScore + $stockScore, 1);
    }

    /**
     * Update progress tracker
     */
    private static function updateProgress(string $phase, int $progress): void
    {
        self::$progress['phase'] = $phase;
        self::$progress['progress'] = $progress;
        usleep(100000); // Simulate processing time (100ms)
    }
}
