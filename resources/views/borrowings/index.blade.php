<x-app-layout>
    <x-slot:title>Peminjaman</x-slot:title>

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 animate-fade-in-up">
            <div>
                <h2 class="text-2xl font-bold dark:text-white text-gray-900">Peminjaman Barang</h2>
                <p class="text-sm dark:text-gray-400 text-gray-500 mt-1">Kelola peminjaman dan pengembalian barang</p>
            </div>
            @role('admin|staff')
            <a href="{{ route('borrowings.create') }}" class="btn btn-primary">
                <i data-lucide="plus" class="w-4 h-4"></i> Tambah Peminjaman
            </a>
            @endrole
        </div>

        <!-- Filters -->
        <div class="glass-card p-4">
            <form method="GET" class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama peminjam..." class="form-input">
                </div>
                <select name="status" class="form-input sm:w-44">
                    <option value="">Semua Status</option>
                    <option value="dipinjam" {{ request('status') === 'dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                    <option value="dikembalikan" {{ request('status') === 'dikembalikan' ? 'selected' : '' }}>Dikembalikan</option>
                    <option value="terlambat" {{ request('status') === 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                </select>
                <button type="submit" class="btn btn-primary"><i data-lucide="search" class="w-4 h-4"></i> Cari</button>
            </form>
        </div>

        <!-- Borrowings Table -->
        <div class="glass-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Peminjam</th>
                            <th>Barang</th>
                            <th>Tgl Pinjam</th>
                            <th>Tgl Kembali</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($borrowings as $i => $borrowing)
                        <tr>
                            <td class="dark:text-gray-500 text-gray-400 text-xs">{{ $borrowings->firstItem() + $i }}</td>
                            <td>
                                <div>
                                    <p class="font-medium dark:text-white text-gray-900">{{ $borrowing->borrower_name }}</p>
                                    <p class="text-xs dark:text-gray-400 text-gray-500">Oleh: {{ $borrowing->user->name ?? '-' }}</p>
                                </div>
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($borrowing->details as $detail)
                                    <span class="text-xs px-2 py-0.5 rounded-full dark:bg-white/5 bg-gray-100 dark:text-gray-300 text-gray-600">
                                        {{ $detail->product->nama_barang ?? 'N/A' }} <span class="dark:text-gray-500 text-gray-400">x{{ $detail->qty }}</span>
                                    </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="text-sm dark:text-gray-300 text-gray-700">{{ $borrowing->tanggal_pinjam->format('d M Y') }}</td>
                            <td class="text-sm dark:text-gray-300 text-gray-700">
                                {{ $borrowing->tanggal_kembali?->format('d M Y') ?? '-' }}
                                @if($borrowing->isOverdue())
                                <span class="text-red-400 text-xs block">{{ now()->diffInDays($borrowing->tanggal_kembali) }} hari terlambat</span>
                                @endif
                            </td>
                            <td><span class="badge {{ $borrowing->status_badge }}">{{ $borrowing->status_label }}</span></td>
                            <td>
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('borrowings.show', $borrowing) }}" class="p-1.5 rounded-lg hover:bg-primary-500/10 text-primary-400 transition-colors">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                    @if($borrowing->status === 'dipinjam')
                                    @role('admin|staff')
                                    <form method="POST" action="{{ route('borrowings.return', $borrowing) }}" onsubmit="return confirm('Konfirmasi pengembalian barang?')">
                                        @csrf
                                        <button type="submit" class="btn btn-success text-xs py-1 px-2">
                                            <i data-lucide="undo-2" class="w-3 h-3"></i> Kembalikan
                                        </button>
                                    </form>
                                    @endrole
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-12">
                                <i data-lucide="arrow-left-right" class="w-12 h-12 dark:text-gray-600 text-gray-300 mx-auto mb-3"></i>
                                <p class="text-sm dark:text-gray-400 text-gray-500">Belum ada data peminjaman.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($borrowings->hasPages())
            <div class="px-6 py-4 border-t dark:border-white/5 border-gray-100">{{ $borrowings->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
