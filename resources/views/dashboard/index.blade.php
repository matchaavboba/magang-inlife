<x-app-layout>
    <x-slot:title>Dashboard</x-slot:title>

    <div class="space-y-6">
        <!-- Welcome -->
        <div class="animate-fade-in-up">
            <h2 class="text-2xl font-bold dark:text-white text-gray-900">
                Selamat datang, {{ Auth::user()->name }} 👋
            </h2>
            <p class="text-sm dark:text-gray-400 text-gray-500 mt-1">
                Ringkasan inventaris kantor {{ now()->format('l, d F Y') }}
            </p>
        </div>

        <!-- Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 stagger-children">
            <!-- Total Barang -->
            <div class="stat-card blue text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-200 text-xs font-medium uppercase tracking-wide">Total Barang</p>
                        <p class="text-3xl font-bold mt-1">{{ number_format($totalBarang) }}</p>
                        <p class="text-blue-200 text-xs mt-1">{{ number_format($totalStok) }} unit stok</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center">
                        <i data-lucide="package" class="w-6 h-6"></i>
                    </div>
                </div>
            </div>

            <!-- Barang Tersedia -->
            <div class="stat-card green text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-200 text-xs font-medium uppercase tracking-wide">Tersedia</p>
                        <p class="text-3xl font-bold mt-1">{{ number_format($barangTersedia) }}</p>
                        <p class="text-green-200 text-xs mt-1">barang bisa dipinjam</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center">
                        <i data-lucide="check-circle" class="w-6 h-6"></i>
                    </div>
                </div>
            </div>

            <!-- Barang Dipinjam -->
            <div class="stat-card amber text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-amber-200 text-xs font-medium uppercase tracking-wide">Dipinjam</p>
                        <p class="text-3xl font-bold mt-1">{{ number_format($barangDipinjam) }}</p>
                        <p class="text-amber-200 text-xs mt-1">peminjaman aktif</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center">
                        <i data-lucide="arrow-left-right" class="w-6 h-6"></i>
                    </div>
                </div>
            </div>

            <!-- Stok Menipis -->
            <div class="stat-card red text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-red-200 text-xs font-medium uppercase tracking-wide">Stok Menipis</p>
                        <p class="text-3xl font-bold mt-1">{{ number_format($stokMenipis) }}</p>
                        <p class="text-red-200 text-xs mt-1">{{ $stokHabis }} stok habis</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center">
                        <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts & Activity Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Borrowing Chart (2/3) -->
            <div class="lg:col-span-2 glass-card p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-semibold dark:text-white text-gray-900">Grafik Peminjaman</h3>
                        <p class="text-xs dark:text-gray-400 text-gray-500">12 bulan terakhir</p>
                    </div>
                    <div class="badge badge-info">
                        <i data-lucide="trending-up" class="w-3 h-3 mr-1"></i> Live
                    </div>
                </div>
                <div style="height: 300px; position: relative;">
                    <canvas id="borrowingChart"></canvas>
                </div>
            </div>

            <!-- Low Stock Alert (1/3) -->
            <div class="glass-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold dark:text-white text-gray-900">⚠️ Stok Menipis</h3>
                    <a href="{{ route('products.index', ['low_stock' => 1]) }}" class="text-xs text-primary-500 hover:text-primary-400 font-medium">
                        Lihat semua →
                    </a>
                </div>
                <div class="space-y-3">
                    @forelse($lowStockProducts as $product)
                    <div class="flex items-center gap-3 p-3 rounded-lg dark:bg-white/3 bg-gray-50">
                        <div class="w-8 h-8 rounded-lg {{ $product->stok === 0 ? 'bg-red-500/20' : 'bg-amber-500/20' }} flex items-center justify-center">
                            <i data-lucide="{{ $product->stok === 0 ? 'x-circle' : 'alert-triangle' }}"
                               class="w-4 h-4 {{ $product->stok === 0 ? 'text-red-400' : 'text-amber-400' }}"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium dark:text-white text-gray-900 truncate">{{ $product->nama_barang }}</p>
                            <p class="text-xs dark:text-gray-400 text-gray-500">{{ $product->category->name ?? '' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold {{ $product->stok === 0 ? 'text-red-400' : 'text-amber-400' }}">{{ $product->stok }}</p>
                            <p class="text-[10px] dark:text-gray-500 text-gray-400">min: {{ $product->min_stok }}</p>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-6">
                        <i data-lucide="check-circle" class="w-8 h-8 text-green-400 mx-auto mb-2"></i>
                        <p class="text-sm dark:text-gray-400 text-gray-500">Semua stok aman! 🎉</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Activity & Overdue -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Activity -->
            <div class="glass-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold dark:text-white text-gray-900">Aktivitas Terbaru</h3>
                    <span class="badge badge-success animate-pulse-glow">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-400 mr-1.5"></span>
                        Real-time
                    </span>
                </div>
                <div class="space-y-2 max-h-[300px] overflow-y-auto">
                    @forelse($recentActivity as $event)
                    <div class="event-item {{ $event->topic }} rounded-lg dark:bg-white/2 bg-gray-50/50">
                        <div class="flex items-center gap-3">
                            @php
                                $icon = match($event->event_type) {
                                    'PRODUCT_CREATED' => 'plus-circle',
                                    'PRODUCT_UPDATED' => 'edit-3',
                                    'STOCK_UPDATED' => 'refresh-cw',
                                    'BORROWING_CREATED' => 'arrow-right',
                                    'BORROWING_RETURNED' => 'arrow-left',
                                    'LOW_STOCK_ALERT' => 'alert-triangle',
                                    default => 'activity'
                                };
                                $color = match($event->topic) {
                                    'inventory' => 'text-blue-400',
                                    'borrowing' => 'text-green-400',
                                    'analytics' => 'text-amber-400',
                                    default => 'text-gray-400'
                                };
                            @endphp
                            <i data-lucide="{{ $icon }}" class="w-4 h-4 {{ $color }} shrink-0"></i>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm dark:text-gray-300 text-gray-700 truncate">
                                    {{ str_replace('_', ' ', $event->event_type) }}
                                </p>
                                <p class="text-xs dark:text-gray-500 text-gray-400">
                                    {{ $event->created_at->diffForHumans() }}
                                </p>
                            </div>
                            <span class="badge {{ $event->processed ? 'badge-success' : 'badge-warning' }} text-[10px]">
                                {{ $event->processed ? 'Processed' : 'Pending' }}
                            </span>
                        </div>
                    </div>
                    @empty
                    <p class="text-sm dark:text-gray-400 text-gray-500 text-center py-4">Belum ada aktivitas.</p>
                    @endforelse
                </div>
            </div>

            <!-- Overdue Borrowings -->
            <div class="glass-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold dark:text-white text-gray-900">⏰ Peminjaman Terlambat</h3>
                    <a href="{{ route('borrowings.index', ['status' => 'dipinjam']) }}" class="text-xs text-primary-500 hover:text-primary-400 font-medium">
                        Lihat semua →
                    </a>
                </div>
                <div class="space-y-3">
                    @forelse($overdueBorrowings as $borrowing)
                    <div class="p-3 rounded-lg dark:bg-red-500/5 bg-red-50 border dark:border-red-500/10 border-red-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium dark:text-white text-gray-900">{{ $borrowing->borrower_name }}</p>
                                <p class="text-xs dark:text-gray-400 text-gray-500 mt-0.5">
                                    Jatuh tempo: {{ $borrowing->tanggal_kembali->format('d M Y') }}
                                    <span class="text-red-400 font-medium">({{ now()->diffInDays($borrowing->tanggal_kembali) }} hari)</span>
                                </p>
                            </div>
                            <span class="badge badge-danger">Terlambat</span>
                        </div>
                        <div class="mt-2 flex flex-wrap gap-1">
                            @foreach($borrowing->details as $detail)
                            <span class="text-[10px] px-1.5 py-0.5 rounded dark:bg-white/5 bg-gray-100 dark:text-gray-300 text-gray-600">
                                {{ $detail->product->nama_barang ?? 'N/A' }} (x{{ $detail->qty }})
                            </span>
                            @endforeach
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-6">
                        <i data-lucide="clock" class="w-8 h-8 text-green-400 mx-auto mb-2"></i>
                        <p class="text-sm dark:text-gray-400 text-gray-500">Tidak ada peminjaman terlambat! 🎉</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js Script -->
    <script>
        window.addEventListener('load', function() {
            const ctx = document.getElementById('borrowingChart');
            if (!ctx) return;

            const chartData = @json($chartData);
            const isDark = document.documentElement.classList.contains('dark');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartData.map(d => d.label),
                    datasets: [{
                        label: 'Peminjaman',
                        data: chartData.map(d => d.count),
                        backgroundColor: chartData.map((_, i) =>
                            `rgba(59, 130, 246, ${0.4 + (i / chartData.length) * 0.6})`
                        ),
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 1,
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                color: isDark ? '#64748b' : '#94a3b8',
                            },
                            grid: {
                                color: isDark ? 'rgba(255,255,255,0.04)' : 'rgba(0,0,0,0.04)',
                            }
                        },
                        x: {
                            ticks: {
                                color: isDark ? '#64748b' : '#94a3b8',
                            },
                            grid: { display: false }
                        }
                    }
                }
            });
        });
    </script>
</x-app-layout>
