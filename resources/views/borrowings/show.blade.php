<x-app-layout>
    <x-slot:title>Detail Peminjaman</x-slot:title>

    <div class="max-w-3xl mx-auto space-y-6 animate-fade-in-up">
        <a href="{{ route('borrowings.index') }}" class="text-sm text-primary-500 hover:text-primary-400 flex items-center gap-1">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
        </a>

        <div class="glass-card p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl font-bold dark:text-white text-gray-900">Peminjaman #{{ $borrowing->id }}</h2>
                    <p class="text-sm dark:text-gray-400 text-gray-500 mt-1">{{ $borrowing->borrower_name }}</p>
                </div>
                <span class="badge {{ $borrowing->status_badge }} text-sm px-3 py-1">{{ $borrowing->status_label }}</span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                <div class="p-3 rounded-lg dark:bg-white/3 bg-gray-50">
                    <p class="text-xs dark:text-gray-500 text-gray-400">Tanggal Pinjam</p>
                    <p class="text-sm font-medium dark:text-white text-gray-900 mt-0.5">{{ $borrowing->tanggal_pinjam->format('d M Y') }}</p>
                </div>
                <div class="p-3 rounded-lg dark:bg-white/3 bg-gray-50">
                    <p class="text-xs dark:text-gray-500 text-gray-400">Estimasi Kembali</p>
                    <p class="text-sm font-medium dark:text-white text-gray-900 mt-0.5">{{ $borrowing->tanggal_kembali?->format('d M Y') ?? '-' }}</p>
                </div>
                <div class="p-3 rounded-lg dark:bg-white/3 bg-gray-50">
                    <p class="text-xs dark:text-gray-500 text-gray-400">Dikembalikan</p>
                    <p class="text-sm font-medium dark:text-white text-gray-900 mt-0.5">{{ $borrowing->tanggal_dikembalikan?->format('d M Y') ?? '-' }}</p>
                </div>
                <div class="p-3 rounded-lg dark:bg-white/3 bg-gray-50">
                    <p class="text-xs dark:text-gray-500 text-gray-400">Dicatat oleh</p>
                    <p class="text-sm font-medium dark:text-white text-gray-900 mt-0.5">{{ $borrowing->user->name ?? '-' }}</p>
                </div>
            </div>

            @if($borrowing->catatan)
            <div class="p-3 rounded-lg dark:bg-white/3 bg-gray-50 mb-6">
                <p class="text-xs dark:text-gray-500 text-gray-400 mb-1">Catatan</p>
                <p class="text-sm dark:text-gray-300 text-gray-700">{{ $borrowing->catatan }}</p>
            </div>
            @endif

            <h3 class="text-sm font-semibold dark:text-white text-gray-900 uppercase tracking-wide mb-3">Barang Dipinjam</h3>
            <div class="space-y-2">
                @foreach($borrowing->details as $detail)
                <div class="flex items-center gap-4 p-3 rounded-lg dark:bg-white/3 bg-gray-50">
                    <div class="w-10 h-10 rounded-lg bg-primary-500/10 flex items-center justify-center">
                        <i data-lucide="package" class="w-5 h-5 text-primary-400"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium dark:text-white text-gray-900">{{ $detail->product->nama_barang ?? 'N/A' }}</p>
                        <p class="text-xs dark:text-gray-400 text-gray-500">
                            {{ $detail->product->kode_barang ?? '' }} · {{ $detail->product->category->name ?? '' }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold dark:text-white text-gray-900">x{{ $detail->qty }}</p>
                        <p class="text-xs dark:text-gray-400 text-gray-500">Kondisi: {{ $detail->kondisi_pinjam }}</p>
                    </div>
                </div>
                @endforeach
            </div>

            @if($borrowing->status === 'dipinjam')
            @role('admin|staff')
            <div class="mt-6 pt-6 border-t dark:border-white/5 border-gray-100">
                <form method="POST" action="{{ route('borrowings.return', $borrowing) }}" onsubmit="return confirm('Konfirmasi pengembalian semua barang?')">
                    @csrf
                    <button type="submit" class="btn btn-success w-full justify-center">
                        <i data-lucide="undo-2" class="w-4 h-4"></i> Kembalikan Semua Barang
                    </button>
                </form>
            </div>
            @endrole
            @endif
        </div>
    </div>
</x-app-layout>
