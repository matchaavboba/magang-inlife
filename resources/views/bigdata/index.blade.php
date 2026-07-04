<x-app-layout>
    <x-slot:title>Big Data Dashboard</x-slot:title>

    <div class="space-y-6" x-data="bigDataDashboard()">
        <!-- Header -->
        <div class="animate-fade-in-up">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-blue-600 flex items-center justify-center">
                    <i data-lucide="database" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold dark:text-white text-gray-900">Big Data Dashboard</h2>
                    <p class="text-sm dark:text-gray-400 text-gray-500">Apache Kafka • Spark Analytics • Hadoop MapReduce</p>
                </div>
            </div>
        </div>

        <!-- Pipeline Visualization -->
        <div class="glass-card p-6 animate-fade-in-up">
            <h3 class="text-lg font-semibold dark:text-white text-gray-900 mb-6 flex items-center gap-2">
                <i data-lucide="git-branch" class="w-5 h-5 text-primary-400"></i>
                Data Pipeline Architecture
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-7 gap-3 items-center">
                <!-- Producer -->
                <div class="pipeline-node kafka col-span-1">
                    <div class="text-2xl mb-1">📤</div>
                    <p class="text-xs font-bold text-blue-300">PRODUCER</p>
                    <p class="text-[10px] text-blue-400/70">User Actions</p>
                </div>
                <div class="hidden md:block pipeline-connector col-span-1"></div>
                <!-- Kafka -->
                <div class="pipeline-node kafka col-span-1">
                    <div class="text-2xl mb-1">🔵</div>
                    <p class="text-xs font-bold text-blue-300">APACHE KAFKA</p>
                    <p class="text-[10px] text-blue-400/70">Event Streaming</p>
                    <p class="text-[10px] text-blue-200 mt-1 font-mono" x-text="totalEvents + ' events'"></p>
                </div>
                <div class="hidden md:block pipeline-connector col-span-1" style="background: linear-gradient(90deg, transparent, #a855f7, transparent);"></div>
                <!-- Spark -->
                <div class="pipeline-node spark col-span-1">
                    <div class="text-2xl mb-1">⚡</div>
                    <p class="text-xs font-bold text-purple-300">APACHE SPARK</p>
                    <p class="text-[10px] text-purple-400/70">Analytics Engine</p>
                </div>
                <div class="hidden md:block pipeline-connector col-span-1" style="background: linear-gradient(90deg, transparent, #f59e0b, transparent);"></div>
                <!-- Hadoop -->
                <div class="pipeline-node hadoop col-span-1">
                    <div class="text-2xl mb-1">🐘</div>
                    <p class="text-xs font-bold text-amber-300">HADOOP</p>
                    <p class="text-[10px] text-amber-400/70">MapReduce</p>
                </div>
            </div>
        </div>

        <!-- Stats Row -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 stagger-children">
            <div class="stat-card blue text-white">
                <p class="text-blue-200 text-xs font-medium uppercase tracking-wide">Total Events</p>
                <p class="text-2xl font-bold mt-1" x-text="totalEvents">{{ $totalEvents }}</p>
                <p class="text-blue-200 text-xs mt-1">Kafka streams</p>
            </div>
            <div class="stat-card green text-white">
                <p class="text-green-200 text-xs font-medium uppercase tracking-wide">Processed</p>
                <p class="text-2xl font-bold mt-1" x-text="processedEvents">{{ $processedEvents }}</p>
                <p class="text-green-200 text-xs mt-1" x-text="processedRate + '% rate'">{{ $totalEvents > 0 ? round(($processedEvents / $totalEvents) * 100, 1) : 0 }}% rate</p>
            </div>
            <div class="stat-card amber text-white">
                <p class="text-amber-200 text-xs font-medium uppercase tracking-wide">Pending</p>
                <p class="text-2xl font-bold mt-1" x-text="pendingEvents">{{ $pendingEvents }}</p>
                <p class="text-amber-200 text-xs mt-1">in queue</p>
            </div>
            <div class="stat-card purple text-white">
                <p class="text-purple-200 text-xs font-medium uppercase tracking-wide">Avg Processing</p>
                <p class="text-2xl font-bold mt-1" x-text="avgTime + 'ms'">{{ round($avgProcessingTime ?? 0) }}ms</p>
                <p class="text-purple-200 text-xs mt-1">per event</p>
            </div>
        </div>

        <!-- Kafka & Spark Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Kafka Topics -->
            <div class="glass-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold dark:text-white text-gray-900 flex items-center gap-2">
                        🔵 Kafka Topics
                    </h3>
                    <div class="flex gap-2">
                        <button @click="produceTestEvent()" class="btn btn-primary text-xs py-1">
                            <i data-lucide="send" class="w-3 h-3"></i> Produce
                        </button>
                        <button @click="consumeEvents()" class="btn btn-success text-xs py-1">
                            <i data-lucide="download" class="w-3 h-3"></i> Consume
                        </button>
                    </div>
                </div>

                <div class="space-y-3">
                    @foreach($kafkaStats as $topic => $stats)
                    <div class="p-4 rounded-xl dark:bg-white/3 bg-gray-50">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full {{ $topic === 'inventory' ? 'bg-blue-400' : ($topic === 'borrowing' ? 'bg-green-400' : 'bg-amber-400') }}"></span>
                                <p class="text-sm font-semibold dark:text-white text-gray-900 font-mono">{{ $topic }}</p>
                            </div>
                            <span class="text-xs dark:text-gray-400 text-gray-500">{{ $stats['throughput_per_min'] }} evt/hr</span>
                        </div>
                        <div class="grid grid-cols-3 gap-2 text-center">
                            <div>
                                <p class="text-lg font-bold dark:text-white text-gray-900">{{ $stats['total_events'] }}</p>
                                <p class="text-[10px] dark:text-gray-500 text-gray-400">Total</p>
                            </div>
                            <div>
                                <p class="text-lg font-bold text-green-400">{{ $stats['processed'] }}</p>
                                <p class="text-[10px] dark:text-gray-500 text-gray-400">Processed</p>
                            </div>
                            <div>
                                <p class="text-lg font-bold text-amber-400">{{ $stats['pending'] }}</p>
                                <p class="text-[10px] dark:text-gray-500 text-gray-400">Pending</p>
                            </div>
                        </div>
                        <!-- Progress bar -->
                        <div class="mt-2 h-1.5 rounded-full dark:bg-white/5 bg-gray-200 overflow-hidden">
                            <div class="h-full rounded-full bg-gradient-to-r from-green-400 to-green-500 transition-all duration-500"
                                 style="width: {{ $stats['processing_rate'] }}%"></div>
                        </div>
                        <p class="text-[10px] dark:text-gray-500 text-gray-400 mt-1 text-right">{{ $stats['processing_rate'] }}% processed</p>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Live Event Stream -->
            <div class="glass-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold dark:text-white text-gray-900 flex items-center gap-2">
                        📡 Live Event Stream
                        <span class="badge badge-success animate-pulse-glow text-[10px]">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-400 mr-1"></span>
                            LIVE
                        </span>
                    </h3>
                    <button @click="refreshEvents()" class="p-1.5 rounded-lg hover:bg-primary-500/10 text-primary-400">
                        <i data-lucide="refresh-cw" class="w-4 h-4" :class="refreshing && 'animate-spin'"></i>
                    </button>
                </div>
                <div class="space-y-1.5 max-h-[400px] overflow-y-auto">
                    @foreach($recentEvents as $event)
                    <div class="event-item {{ $event['topic'] ?? '' }} rounded-lg text-xs dark:bg-white/2 bg-gray-50/50">
                        <div class="flex items-center gap-2">
                            @php
                                $topicColor = match($event['topic'] ?? '') {
                                    'inventory' => 'text-blue-400',
                                    'borrowing' => 'text-green-400',
                                    'analytics' => 'text-amber-400',
                                    default => 'text-gray-400'
                                };
                            @endphp
                            <span class="font-mono {{ $topicColor }} font-bold">{{ strtoupper($event['topic'] ?? 'N/A') }}</span>
                            <span class="dark:text-gray-500 text-gray-400">|</span>
                            <span class="dark:text-gray-300 text-gray-700 flex-1 truncate">{{ $event['event_type'] ?? '' }}</span>
                            <span class="badge {{ ($event['processed'] ?? false) ? 'badge-success' : 'badge-warning' }} text-[9px] px-1.5">
                                {{ ($event['processed'] ?? false) ? '✓' : '⏳' }}
                            </span>
                            <span class="dark:text-gray-600 text-gray-400 text-[10px] tabular-nums whitespace-nowrap">
                                {{ isset($event['created_at']) ? \Carbon\Carbon::parse($event['created_at'])->diffForHumans(null, true, true) : '' }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Spark Analytics Section -->
        <div class="glass-card p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold dark:text-white text-gray-900 flex items-center gap-2">
                    ⚡ Spark Analytics Engine
                </h3>
                <button @click="runSparkAnalysis()" :disabled="sparkLoading" class="btn btn-primary">
                    <i data-lucide="play" class="w-4 h-4" :class="sparkLoading && 'animate-spin'"></i>
                    <span x-text="sparkLoading ? 'Processing...' : 'Run Analysis'"></span>
                </button>
            </div>

            <!-- Spark Results -->
            <div x-show="sparkResults" x-transition style="display: none;">
                <!-- Processing Metadata -->
                <div class="mb-4 p-3 rounded-lg dark:bg-green-500/5 bg-green-50 border dark:border-green-500/10 border-green-100">
                    <p class="text-sm text-green-400 flex items-center gap-2">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        Analysis complete in <span class="font-bold font-mono" x-text="sparkResults?._metadata?.processing_time_ms + 'ms'"></span>
                        — <span x-text="sparkResults?._metadata?.data_points_analyzed"></span> data points analyzed
                    </p>
                </div>

                <!-- Results Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Borrowing Trend -->
                    <div class="p-4 rounded-xl dark:bg-white/3 bg-gray-50">
                        <h4 class="text-sm font-semibold dark:text-white text-gray-900 mb-3 flex items-center gap-2">
                            📈 Tren Peminjaman
                            <span class="badge badge-info text-[10px]" x-text="sparkResults?.borrowing_trends?.trend ?? ''"></span>
                        </h4>
                        <canvas id="sparkTrendChart" height="150"></canvas>
                    </div>

                    <!-- Category Distribution -->
                    <div class="p-4 rounded-xl dark:bg-white/3 bg-gray-50">
                        <h4 class="text-sm font-semibold dark:text-white text-gray-900 mb-3">📊 Distribusi Kategori</h4>
                        <canvas id="sparkCategoryChart" height="150"></canvas>
                    </div>

                    <!-- Top Borrowed -->
                    <div class="p-4 rounded-xl dark:bg-white/3 bg-gray-50">
                        <h4 class="text-sm font-semibold dark:text-white text-gray-900 mb-3">🔥 Barang Paling Sering Dipinjam</h4>
                        <div class="space-y-2">
                            <template x-for="item in (sparkResults?.top_borrowed_items ?? []).slice(0, 5)" :key="item.product_id">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1">
                                        <p class="text-xs dark:text-gray-300 text-gray-700 truncate" x-text="item.nama_barang"></p>
                                    </div>
                                    <span class="text-xs font-mono font-bold dark:text-primary-400 text-primary-600" x-text="item.total_borrowed + 'x'"></span>
                                    <div class="w-20 h-1.5 rounded-full dark:bg-white/5 bg-gray-200">
                                        <div class="h-full rounded-full bg-primary-500" :style="'width:' + Math.min(100, (item.total_borrowed / (sparkResults?.top_borrowed_items?.[0]?.total_borrowed || 1)) * 100) + '%'"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Stock Forecast -->
                    <div class="p-4 rounded-xl dark:bg-white/3 bg-gray-50">
                        <h4 class="text-sm font-semibold dark:text-white text-gray-900 mb-3">📦 Stock Forecast (Risk)</h4>
                        <div class="space-y-2">
                            <template x-for="item in (sparkResults?.stock_forecast ?? []).slice(0, 5)" :key="item.product_id">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full" :class="{
                                        'bg-red-500': item.risk_level === 'critical',
                                        'bg-amber-500': item.risk_level === 'high',
                                        'bg-yellow-500': item.risk_level === 'medium',
                                        'bg-green-500': item.risk_level === 'low'
                                    }"></span>
                                    <p class="text-xs dark:text-gray-300 text-gray-700 flex-1 truncate" x-text="item.nama_barang"></p>
                                    <span class="text-[10px] font-mono dark:text-gray-400 text-gray-500" x-text="'stok: ' + item.current_stock"></span>
                                    <span class="badge text-[9px]" :class="{
                                        'badge-danger': item.risk_level === 'critical' || item.risk_level === 'high',
                                        'badge-warning': item.risk_level === 'medium',
                                        'badge-success': item.risk_level === 'low'
                                    }" x-text="item.risk_level"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div x-show="!sparkResults && !sparkLoading" class="text-center py-8">
                <div class="w-16 h-16 rounded-2xl bg-purple-500/10 flex items-center justify-center mx-auto mb-3">
                    <span class="text-3xl">⚡</span>
                </div>
                <p class="text-sm dark:text-gray-400 text-gray-500">Klik "Run Analysis" untuk menjalankan Spark Analytics Engine</p>
                <p class="text-xs dark:text-gray-500 text-gray-400 mt-1">Analisis tren, forecasting, dan distribusi data inventaris</p>
            </div>
        </div>

        <!-- Hadoop MapReduce Section -->
        <div class="glass-card p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold dark:text-white text-gray-900 flex items-center gap-2">
                    🐘 Hadoop MapReduce Engine
                </h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
                @foreach($hadoopJobs as $job)
                <button @click="runHadoopJob('{{ $job['id'] }}')" :disabled="hadoopLoading"
                        class="p-4 rounded-xl dark:bg-white/3 bg-gray-50 text-left hover:ring-2 hover:ring-primary-500/30 transition-all group">
                    <div class="text-2xl mb-2">{{ $job['icon'] }}</div>
                    <p class="text-sm font-semibold dark:text-white text-gray-900 group-hover:text-primary-400 transition-colors">{{ $job['name'] }}</p>
                    <p class="text-xs dark:text-gray-400 text-gray-500 mt-1">{{ $job['description'] }}</p>
                </button>
                @endforeach
            </div>

            <!-- Hadoop Results -->
            <div x-show="hadoopResults" x-transition style="display: none;">
                <div class="mb-3 p-3 rounded-lg dark:bg-amber-500/5 bg-amber-50 border dark:border-amber-500/10 border-amber-100">
                    <p class="text-sm text-amber-400 flex items-center gap-2">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        MapReduce job complete —
                        Phase: <span class="font-mono font-bold" x-text="hadoopResults?._metadata?.phases?.join(' → ')"></span>
                        — <span class="font-mono font-bold" x-text="hadoopResults?._metadata?.processing_time_ms + 'ms'"></span>
                    </p>
                </div>

                <!-- Results Table -->
                <div class="overflow-x-auto rounded-xl">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <template x-for="key in hadoopColumns" :key="key">
                                    <th x-text="key.replace(/_/g, ' ').toUpperCase()" class="text-[10px]"></th>
                                </template>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, idx) in hadoopRows" :key="idx">
                                <tr>
                                    <template x-for="key in hadoopColumns" :key="key">
                                        <td class="text-xs">
                                            <span x-text="Array.isArray(row[key]) ? row[key].join(', ') : row[key]"></span>
                                        </td>
                                    </template>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <div x-show="!hadoopResults && !hadoopLoading" class="text-center py-6">
                <p class="text-sm dark:text-gray-400 text-gray-500">Pilih job MapReduce di atas untuk menjalankan analisis</p>
            </div>

            <div x-show="hadoopLoading" class="text-center py-8" style="display: none;">
                <div class="flex items-center justify-center gap-3">
                    <div class="w-6 h-6 border-2 border-amber-500 border-t-transparent rounded-full animate-spin"></div>
                    <p class="text-sm dark:text-gray-300 text-gray-700">Running MapReduce job... <span class="font-mono text-amber-400" x-text="hadoopPhase"></span></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function bigDataDashboard() {
            return {
                totalEvents: {{ $totalEvents }},
                processedEvents: {{ $processedEvents }},
                pendingEvents: {{ $pendingEvents }},
                avgTime: {{ round($avgProcessingTime ?? 0) }},
                processedRate: {{ $totalEvents > 0 ? round(($processedEvents / $totalEvents) * 100, 1) : 0 }},
                refreshing: false,

                // Spark
                sparkLoading: false,
                sparkResults: null,
                sparkTrendChart: null,
                sparkCategoryChart: null,

                // Hadoop
                hadoopLoading: false,
                hadoopResults: null,
                hadoopPhase: '',
                hadoopColumns: [],
                hadoopRows: [],

                async refreshEvents() {
                    this.refreshing = true;
                    try {
                        const res = await axios.get('{{ route("bigdata.stats") }}');
                        this.totalEvents = res.data.total_events;
                        this.processedEvents = res.data.processed;
                        this.pendingEvents = res.data.pending;
                        this.avgTime = res.data.avg_processing_time;
                        this.processedRate = this.totalEvents > 0
                            ? Math.round((this.processedEvents / this.totalEvents) * 1000) / 10
                            : 0;
                    } catch (e) { console.error(e); }
                    this.refreshing = false;
                },

                async produceTestEvent() {
                    try {
                        const topics = ['inventory', 'borrowing', 'analytics'];
                        const topic = topics[Math.floor(Math.random() * topics.length)];
                        await axios.post('{{ route("bigdata.kafka.produce") }}', {
                            topic: topic,
                            event_type: 'TEST_EVENT',
                            payload: { source: 'manual_test', timestamp: new Date().toISOString() }
                        });
                        Alpine.store('toast').fire('Event produced to "' + topic + '" topic!', 'success');
                        this.refreshEvents();
                    } catch (e) {
                        Alpine.store('toast').fire('Failed to produce event', 'error');
                    }
                },

                async consumeEvents() {
                    try {
                        const res = await axios.post('{{ route("bigdata.kafka.consume") }}', { topic: 'inventory', limit: 5 });
                        Alpine.store('toast').fire('Consumed ' + res.data.processed + ' events', 'success');
                        this.refreshEvents();
                    } catch (e) {
                        Alpine.store('toast').fire('Failed to consume events', 'error');
                    }
                },

                async runSparkAnalysis() {
                    this.sparkLoading = true;
                    this.sparkResults = null;
                    try {
                        const res = await axios.post('{{ route("bigdata.spark.analyze") }}');
                        this.sparkResults = res.data.data;
                        this.$nextTick(() => {
                            this.renderSparkCharts();
                        });
                        Alpine.store('toast').fire('Spark analysis complete!', 'success');
                    } catch (e) {
                        Alpine.store('toast').fire('Spark analysis failed', 'error');
                    }
                    this.sparkLoading = false;
                },

                renderSparkCharts() {
                    // Trend Chart
                    const trendCtx = document.getElementById('sparkTrendChart');
                    if (trendCtx && this.sparkResults?.borrowing_trends?.data) {
                        if (this.sparkTrendChart) this.sparkTrendChart.destroy();
                        this.sparkTrendChart = new Chart(trendCtx, {
                            type: 'line',
                            data: {
                                labels: this.sparkResults.borrowing_trends.data.map(d => d.label),
                                datasets: [{
                                    label: 'Peminjaman',
                                    data: this.sparkResults.borrowing_trends.data.map(d => d.total_borrowings),
                                    borderColor: '#3b82f6',
                                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                    fill: true,
                                    tension: 0.4,
                                    pointRadius: 3,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { legend: { display: false } },
                                scales: {
                                    y: { beginAtZero: true, ticks: { stepSize: 1 } },
                                    x: { grid: { display: false } }
                                }
                            }
                        });
                    }

                    // Category Chart
                    const catCtx = document.getElementById('sparkCategoryChart');
                    if (catCtx && this.sparkResults?.category_distribution) {
                        if (this.sparkCategoryChart) this.sparkCategoryChart.destroy();
                        const colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#a855f7', '#06b6d4'];
                        this.sparkCategoryChart = new Chart(catCtx, {
                            type: 'doughnut',
                            data: {
                                labels: this.sparkResults.category_distribution.map(d => d.name),
                                datasets: [{
                                    data: this.sparkResults.category_distribution.map(d => d.total_stock),
                                    backgroundColor: colors,
                                    borderWidth: 0,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { position: 'right', labels: { boxWidth: 10, font: { size: 10 } } }
                                },
                                cutout: '65%',
                            }
                        });
                    }
                },

                async runHadoopJob(jobType) {
                    this.hadoopLoading = true;
                    this.hadoopResults = null;
                    this.hadoopPhase = 'INITIALIZING';

                    // Simulate phase progression
                    const phases = ['SPLIT', 'MAP', 'SHUFFLE', 'REDUCE', 'OUTPUT'];
                    let phaseIdx = 0;
                    const phaseInterval = setInterval(() => {
                        if (phaseIdx < phases.length) {
                            this.hadoopPhase = phases[phaseIdx++];
                        }
                    }, 300);

                    try {
                        const res = await axios.post('{{ route("bigdata.hadoop.run") }}', { job_type: jobType });
                        clearInterval(phaseInterval);
                        this.hadoopResults = res.data.data;
                        this.hadoopPhase = 'COMPLETE';

                        // Extract columns and rows from results
                        const data = this.hadoopResults?.data;
                        if (Array.isArray(data) && data.length > 0) {
                            this.hadoopColumns = Object.keys(data[0]).filter(k => k !== 'items' && !k.startsWith('_'));
                            this.hadoopRows = data;
                        } else if (data && typeof data === 'object') {
                            // Handle nested object results (like stock_aggregation)
                            const summary = data.summary || data;
                            this.hadoopColumns = Object.keys(summary);
                            this.hadoopRows = [summary];
                        }

                        Alpine.store('toast').fire('MapReduce job "' + jobType + '" complete!', 'success');
                        this.refreshEvents();
                    } catch (e) {
                        clearInterval(phaseInterval);
                        Alpine.store('toast').fire('MapReduce job failed', 'error');
                    }
                    this.hadoopLoading = false;
                },

                init() {
                    // Auto-refresh stats every 10 seconds
                    setInterval(() => this.refreshEvents(), 10000);
                }
            }
        }
    </script>
</x-app-layout>
