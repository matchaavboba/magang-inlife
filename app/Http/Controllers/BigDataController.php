<?php

namespace App\Http\Controllers;

use App\BigData\HadoopMapReduce;
use App\BigData\KafkaSimulator;
use App\BigData\SparkAnalytics;
use App\Models\EventLog;
use Illuminate\Http\Request;

class BigDataController extends Controller
{
    /**
     * Big Data Dashboard — main view
     */
    public function index()
    {
        // Kafka stats
        $kafkaStats = KafkaSimulator::getTopicStats();
        $recentEvents = KafkaSimulator::getRecentEvents(15);

        // Total event metrics
        $totalEvents = EventLog::count();
        $processedEvents = EventLog::where('processed', true)->count();
        $pendingEvents = $totalEvents - $processedEvents;
        $avgProcessingTime = EventLog::where('processed', true)->avg('processing_time_ms');

        // Hadoop available jobs
        $hadoopJobs = HadoopMapReduce::getAvailableJobs();

        return view('bigdata.index', compact(
            'kafkaStats',
            'recentEvents',
            'totalEvents',
            'processedEvents',
            'pendingEvents',
            'avgProcessingTime',
            'hadoopJobs'
        ));
    }

    /**
     * Run Spark Analytics
     */
    public function runSparkAnalysis()
    {
        $results = SparkAnalytics::runFullAnalysis();

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }

    /**
     * Run Hadoop MapReduce Job
     */
    public function runHadoopJob(Request $request)
    {
        $request->validate([
            'job_type' => 'required|in:asset_by_location,condition_by_category,borrowing_cross_reference,stock_aggregation',
        ]);

        $results = HadoopMapReduce::runJob($request->job_type);

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }

    /**
     * Produce a test Kafka event
     */
    public function produceEvent(Request $request)
    {
        $request->validate([
            'topic' => 'required|in:inventory,borrowing,analytics',
            'event_type' => 'required|string',
            'payload' => 'nullable|array',
        ]);

        $event = KafkaSimulator::produce(
            $request->topic,
            $request->event_type,
            $request->payload ?? ['test' => true, 'source' => 'manual'],
        );

        return response()->json([
            'success' => true,
            'event' => $event,
        ]);
    }

    /**
     * Consume and process pending events
     */
    public function consumeEvents(Request $request)
    {
        $topic = $request->get('topic', 'inventory');
        $limit = $request->get('limit', 5);

        $events = KafkaSimulator::consume($topic, $limit);
        $processed = 0;

        foreach ($events as $event) {
            KafkaSimulator::acknowledge($event['id'], [
                'status' => 'success',
                'consumer' => 'web-consumer',
                'output' => 'Event processed via web dashboard',
            ]);
            $processed++;
        }

        return response()->json([
            'success' => true,
            'consumed' => count($events),
            'processed' => $processed,
        ]);
    }

    /**
     * Get live event stream (for polling)
     */
    public function eventStream(Request $request)
    {
        $since = $request->get('since');
        $limit = $request->get('limit', 10);

        $query = EventLog::orderBy('created_at', 'desc')->limit($limit);

        if ($since) {
            $query->where('created_at', '>', $since);
        }

        $events = $query->get();

        return response()->json([
            'events' => $events,
            'timestamp' => now()->toISOString(),
            'count' => $events->count(),
        ]);
    }

    /**
     * Get Kafka topic stats (for polling)
     */
    public function getStats()
    {
        return response()->json([
            'kafka' => KafkaSimulator::getTopicStats(),
            'total_events' => EventLog::count(),
            'processed' => EventLog::where('processed', true)->count(),
            'pending' => EventLog::where('processed', false)->count(),
            'avg_processing_time' => round(EventLog::where('processed', true)->avg('processing_time_ms') ?? 0),
            'timestamp' => now()->toISOString(),
        ]);
    }
}
