<?php

namespace App\BigData;

use App\Models\EventLog;
use Illuminate\Support\Facades\Log;

/**
 * KafkaSimulator — Simulates Apache Kafka Event Streaming
 *
 * In a real Kafka setup, events would be published to topics and consumed
 * by consumers. Here we simulate this using Laravel's database and queue system.
 *
 * Architecture:
 * [Producer] --> [Topic Queue (DB)] --> [Consumer/Processor]
 *
 * Topics:
 * - inventory: Product CRUD events
 * - borrowing: Borrowing lifecycle events
 * - analytics: Computed metrics and alerts
 */
class KafkaSimulator
{
    /** Available Kafka topics */
    const TOPIC_INVENTORY = 'inventory';
    const TOPIC_BORROWING = 'borrowing';
    const TOPIC_ANALYTICS = 'analytics';

    /** Event types */
    const EVENT_PRODUCT_CREATED = 'PRODUCT_CREATED';
    const EVENT_PRODUCT_UPDATED = 'PRODUCT_UPDATED';
    const EVENT_PRODUCT_DELETED = 'PRODUCT_DELETED';
    const EVENT_STOCK_UPDATED = 'STOCK_UPDATED';
    const EVENT_BORROWING_CREATED = 'BORROWING_CREATED';
    const EVENT_BORROWING_RETURNED = 'BORROWING_RETURNED';
    const EVENT_LOW_STOCK_ALERT = 'LOW_STOCK_ALERT';
    const EVENT_OVERDUE_ALERT = 'OVERDUE_ALERT';

    /**
     * Produce (publish) an event to a topic
     */
    public static function produce(string $topic, string $eventType, array $data, ?string $refType = null, ?int $refId = null): EventLog
    {
        $startTime = microtime(true);

        $event = EventLog::create([
            'event_type' => $eventType,
            'topic' => $topic,
            'payload' => array_merge($data, [
                'timestamp' => now()->toISOString(),
                'producer' => 'laravel-kafka-simulator',
                'version' => '1.0',
            ]),
            'processed' => false,
            'reference_type' => $refType,
            'reference_id' => $refId,
        ]);

        $elapsed = (microtime(true) - $startTime) * 1000;

        Log::channel('daily')->info("[Kafka] Produced event: {$eventType} to topic: {$topic}", [
            'event_id' => $event->id,
            'time_ms' => round($elapsed, 2),
        ]);

        return $event;
    }

    /**
     * Consume (read) events from a topic
     */
    public static function consume(string $topic, int $limit = 10, bool $onlyUnprocessed = true): array
    {
        $query = EventLog::byTopic($topic)->orderBy('created_at', 'asc');

        if ($onlyUnprocessed) {
            $query->unprocessed();
        }

        return $query->limit($limit)->get()->toArray();
    }

    /**
     * Process (acknowledge) a consumed event
     */
    public static function acknowledge(int $eventId, array $result = []): bool
    {
        $event = EventLog::find($eventId);
        if (!$event) return false;

        $startTime = microtime(true);

        // Simulate processing delay (50-500ms)
        usleep(rand(50000, 500000));

        $elapsed = (microtime(true) - $startTime) * 1000;

        $event->update([
            'processed' => true,
            'result' => array_merge($result, [
                'processed_at' => now()->toISOString(),
                'consumer' => 'laravel-kafka-consumer',
            ]),
            'processing_time_ms' => round($elapsed),
        ]);

        return true;
    }

    /**
     * Get topic statistics
     */
    public static function getTopicStats(): array
    {
        $topics = [self::TOPIC_INVENTORY, self::TOPIC_BORROWING, self::TOPIC_ANALYTICS];
        $stats = [];

        foreach ($topics as $topic) {
            $total = EventLog::byTopic($topic)->count();
            $processed = EventLog::byTopic($topic)->where('processed', true)->count();
            $pending = $total - $processed;
            $avgTime = EventLog::byTopic($topic)
                ->where('processed', true)
                ->avg('processing_time_ms');

            $stats[$topic] = [
                'total_events' => $total,
                'processed' => $processed,
                'pending' => $pending,
                'processing_rate' => $total > 0 ? round(($processed / $total) * 100, 1) : 0,
                'avg_processing_time_ms' => round($avgTime ?? 0),
                'throughput_per_min' => EventLog::byTopic($topic)
                    ->where('created_at', '>=', now()->subMinutes(60))
                    ->count(),
            ];
        }

        return $stats;
    }

    /**
     * Get recent events across all topics
     */
    public static function getRecentEvents(int $limit = 20): array
    {
        return EventLog::recent($limit)->get()->toArray();
    }

    /**
     * Produce inventory event when product is created/updated/deleted
     */
    public static function onProductChange(string $action, array $productData, int $productId): EventLog
    {
        $eventType = match ($action) {
            'created' => self::EVENT_PRODUCT_CREATED,
            'updated' => self::EVENT_PRODUCT_UPDATED,
            'deleted' => self::EVENT_PRODUCT_DELETED,
            default => self::EVENT_STOCK_UPDATED,
        };

        return self::produce(
            self::TOPIC_INVENTORY,
            $eventType,
            ['product' => $productData, 'action' => $action],
            'product',
            $productId
        );
    }

    /**
     * Produce borrowing event
     */
    public static function onBorrowingChange(string $action, array $borrowingData, int $borrowingId): EventLog
    {
        $eventType = match ($action) {
            'created' => self::EVENT_BORROWING_CREATED,
            'returned' => self::EVENT_BORROWING_RETURNED,
            default => self::EVENT_BORROWING_CREATED,
        };

        return self::produce(
            self::TOPIC_BORROWING,
            $eventType,
            ['borrowing' => $borrowingData, 'action' => $action],
            'borrowing',
            $borrowingId
        );
    }

    /**
     * Produce low stock alert
     */
    public static function alertLowStock(array $productData, int $productId): EventLog
    {
        return self::produce(
            self::TOPIC_ANALYTICS,
            self::EVENT_LOW_STOCK_ALERT,
            [
                'product' => $productData,
                'severity' => 'warning',
                'message' => "Stok {$productData['nama_barang']} di bawah minimum ({$productData['stok']}/{$productData['min_stok']})",
            ],
            'product',
            $productId
        );
    }
}
