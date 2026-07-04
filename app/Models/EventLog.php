<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_type',
        'topic',
        'payload',
        'processed',
        'result',
        'processing_time_ms',
        'reference_type',
        'reference_id',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'result' => 'array',
            'processed' => 'boolean',
            'processing_time_ms' => 'integer',
        ];
    }

    /**
     * Scope: unprocessed events
     */
    public function scopeUnprocessed($query)
    {
        return $query->where('processed', false);
    }

    /**
     * Scope: by topic
     */
    public function scopeByTopic($query, string $topic)
    {
        return $query->where('topic', $topic);
    }

    /**
     * Scope: recent events
     */
    public function scopeRecent($query, int $limit = 50)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }
}
