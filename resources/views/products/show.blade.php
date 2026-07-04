<x-app-layout>
    <x-slot:title>Detail Barang</x-slot:title>

    <div class="max-w-4xl mx-auto space-y-6 animate-fade-in-up">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('products.index') }}" class="text-sm text-primary-500 hover:text-primary-400 flex items-center gap-1 mb-2">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
                </a>
                <h2 class="text-2xl font-bold dark:text-white text-gray-900">{{ $product->nama_barang }}</h2>
                <p class="text-sm font-mono dark:text-primary-400 text-primary-600 mt-1">{{ $product->kode_barang }}</p>
            </div>
            @role('admin|staff')
            <div class="flex gap-2">
                <a href="{{ route('products.edit', $product) }}" class="btn btn-secondary">
                    <i data-lucide="edit-3" class="w-4 h-4"></i> Edit
                </a>
            </div>
            @endrole
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Product Image & Info -->
            <div class="glass-card p-6">
                @if($product->gambar)
                <img src="{{ Storage::url($product->gambar) }}" alt="{{ $product->nama_barang }}"
                     class="w-full h-48 object-cover rounded-xl mb-4">
                @else
                <div class="w-full h-48 rounded-xl bg-gradient-to-br from-primary-500/20 to-primary-700/20 flex items-center justify-center mb-4">
                    <i data-lucide="package" class="w-16 h-16 text-primary-400"></i>
                </div>
                @endif

                <div class="space-y-3">
                    <div>
                        <p class="text-xs dark:text-gray-500 text-gray-400 uppercase tracking-wide">Kategori</p>
                        <p class="text-sm font-medium dark:text-white text-gray-900">{{ $product->category->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs dark:text-gray-500 text-gray-400 uppercase tracking-wide">Kondisi</p>
                        @php
                            $kb = match($product->kondisi) { 'baik' => 'badge-success', 'rusak_ringan' => 'badge-warning', default => 'badge-danger' };
                        @endphp
                        <span class="badge {{ $kb }}">{{ ucfirst(str_replace('_', ' ', $product->kondisi)) }}</span>
                    </div>
                    <div>
                        <p class="text-xs dark:text-gray-500 text-gray-400 uppercase tracking-wide">Lokasi</p>
                        <p class="text-sm dark:text-gray-300 text-gray-700">{{ $product->lokasi ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs dark:text-gray-500 text-gray-400 uppercase tracking-wide">Deskripsi</p>
                        <p class="text-sm dark:text-gray-300 text-gray-700">{{ $product->deskripsi ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Stock Info & Borrowing History -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Stock Card -->
                <div class="glass-card p-6">
                    <h3 class="text-lg font-semibold dark:text-white text-gray-900 mb-4">Informasi Stok</h3>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="text-center p-4 rounded-xl dark:bg-white/3 bg-gray-50">
                            <p class="text-3xl font-bold {{ $product->isLowStock() ? 'text-amber-400' : 'dark:text-white text-gray-900' }}">{{ $product->stok }}</p>
                            <p class="text-xs dark:text-gray-400 text-gray-500 mt-1">Total Stok</p>
                        </div>
                        <div class="text-center p-4 rounded-xl dark:bg-white/3 bg-gray-50">
                            <p class="text-3xl font-bold dark:text-white text-gray-900">{{ $product->min_stok }}</p>
                            <p class="text-xs dark:text-gray-400 text-gray-500 mt-1">Min. Stok</p>
                        </div>
                        <div class="text-center p-4 rounded-xl dark:bg-white/3 bg-gray-50">
                            <p class="text-3xl font-bold text-primary-400">{{ $product->available_stock }}</p>
                            <p class="text-xs dark:text-gray-400 text-gray-500 mt-1">Tersedia</p>
                        </div>
                    </div>
                    @if($product->isLowStock())
                    <div class="mt-4 p-3 rounded-lg bg-amber-500/10 border border-amber-500/20">
                        <p class="text-sm text-amber-400 flex items-center gap-2">
                            <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                            Stok di bawah minimum! Segera restock.
                        </p>
                    </div>
                    @endif
                </div>

                <!-- Borrowing History -->
                <div class="glass-card p-6">
                    <h3 class="text-lg font-semibold dark:text-white text-gray-900 mb-4">Riwayat Peminjaman</h3>
                    <div class="space-y-3">
                        @forelse($product->borrowingDetails->sortByDesc('created_at')->take(10) as $detail)
                        <div class="flex items-center gap-3 p-3 rounded-lg dark:bg-white/3 bg-gray-50">
                            <div class="w-8 h-8 rounded-full {{ $detail->borrowing->status === 'dipinjam' ? 'bg-amber-500/20' : 'bg-green-500/20' }} flex items-center justify-center">
                                <i data-lucide="{{ $detail->borrowing->status === 'dipinjam' ? 'arrow-right' : 'check' }}"
                                   class="w-4 h-4 {{ $detail->borrowing->status === 'dipinjam' ? 'text-amber-400' : 'text-green-400' }}"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium dark:text-white text-gray-900">{{ $detail->borrowing->borrower_name }}</p>
                                <p class="text-xs dark:text-gray-400 text-gray-500">
                                    {{ $detail->borrowing->tanggal_pinjam->format('d M Y') }}
                                    · x{{ $detail->qty }}
                                </p>
                            </div>
                            <span class="badge {{ $detail->borrowing->status_badge }}">{{ $detail->borrowing->status_label }}</span>
                        </div>
                        @empty
                        <p class="text-sm dark:text-gray-400 text-gray-500 text-center py-4">Belum pernah dipinjam.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
