<x-app-layout>
    <x-slot:title>Laporan</x-slot:title>

    <div class="space-y-6 animate-fade-in-up">
        <div>
            <h2 class="text-2xl font-bold dark:text-white text-gray-900">Laporan & Export</h2>
            <p class="text-sm dark:text-gray-400 text-gray-500 mt-1">Export data inventaris ke PDF atau Excel</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <!-- Inventaris Report -->
            <div class="glass-card p-6">
                <div class="w-12 h-12 rounded-xl bg-blue-500/10 flex items-center justify-center mb-4">
                    <i data-lucide="package" class="w-6 h-6 text-blue-400"></i>
                </div>
                <h3 class="text-lg font-semibold dark:text-white text-gray-900">Laporan Inventaris</h3>
                <p class="text-sm dark:text-gray-400 text-gray-500 mt-1 mb-6">Data lengkap semua barang inventaris kantor</p>
                <div class="flex gap-3">
                    <a href="{{ route('reports.products.pdf') }}" class="btn btn-danger flex-1 justify-center">
                        <i data-lucide="file-text" class="w-4 h-4"></i> PDF
                    </a>
                    <a href="{{ route('reports.products.excel') }}" class="btn btn-success flex-1 justify-center">
                        <i data-lucide="table" class="w-4 h-4"></i> Excel
                    </a>
                </div>
            </div>

            <!-- Peminjaman Report -->
            <div class="glass-card p-6">
                <div class="w-12 h-12 rounded-xl bg-green-500/10 flex items-center justify-center mb-4">
                    <i data-lucide="arrow-left-right" class="w-6 h-6 text-green-400"></i>
                </div>
                <h3 class="text-lg font-semibold dark:text-white text-gray-900">Laporan Peminjaman</h3>
                <p class="text-sm dark:text-gray-400 text-gray-500 mt-1 mb-6">Riwayat peminjaman dan pengembalian barang</p>
                <div class="flex gap-3">
                    <a href="{{ route('reports.borrowings.pdf') }}" class="btn btn-danger flex-1 justify-center">
                        <i data-lucide="file-text" class="w-4 h-4"></i> PDF
                    </a>
                    <a href="{{ route('reports.borrowings.excel') }}" class="btn btn-success flex-1 justify-center">
                        <i data-lucide="table" class="w-4 h-4"></i> Excel
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
