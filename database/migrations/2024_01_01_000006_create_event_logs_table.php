<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_type');           // e.g., STOCK_UPDATED, BORROWING_CREATED
            $table->string('topic');                 // Kafka topic: inventory, borrowing, analytics
            $table->json('payload');                 // Event data
            $table->boolean('processed')->default(false);
            $table->json('result')->nullable();      // Processing result from Spark/Hadoop
            $table->integer('processing_time_ms')->nullable();
            $table->string('reference_type')->nullable(); // product, borrowing, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->timestamps();

            $table->index(['topic', 'processed']);
            $table->index(['event_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_logs');
    }
};
