<x-app-layout>
    <x-slot:title>Tambah Peminjaman</x-slot:title>

    <div class="max-w-2xl mx-auto space-y-6 animate-fade-in-up" x-data="borrowingForm()">
        <div>
            <h2 class="text-2xl font-bold dark:text-white text-gray-900">Tambah Peminjaman</h2>
            <p class="text-sm dark:text-gray-400 text-gray-500 mt-1">Buat catatan peminjaman barang baru</p>
        </div>

        <form method="POST" action="{{ route('borrowings.store') }}" class="glass-card p-6 space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-1">Nama Peminjam</label>
                    <input type="text" name="borrower_name" value="{{ old('borrower_name') }}" class="form-input" placeholder="Nama lengkap" required>
                    @error('borrower_name') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-1">Tanggal Pinjam</label>
                    <input type="date" name="tanggal_pinjam" value="{{ old('tanggal_pinjam', now()->format('Y-m-d')) }}" class="form-input" required>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-1">Tanggal Kembali (Estimasi)</label>
                    <input type="date" name="tanggal_kembali" value="{{ old('tanggal_kembali') }}" class="form-input">
                </div>
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-1">Catatan</label>
                    <input type="text" name="catatan" value="{{ old('catatan') }}" class="form-input" placeholder="Keperluan...">
                </div>
            </div>

            <!-- Items -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <label class="text-sm font-medium dark:text-gray-300 text-gray-700">Barang yang Dipinjam</label>
                    <button type="button" @click="addItem()" class="text-xs text-primary-500 hover:text-primary-400 font-medium flex items-center gap-1">
                        <i data-lucide="plus" class="w-3 h-3"></i> Tambah Item
                    </button>
                </div>

                <div class="space-y-3">
                    <template x-for="(item, index) in items" :key="index">
                        <div class="flex gap-3 items-start p-3 rounded-lg dark:bg-white/3 bg-gray-50">
                            <div class="flex-1">
                                <select :name="'items[' + index + '][product_id]'" x-model="item.product_id" class="form-input text-sm" required>
                                    <option value="">Pilih Barang</option>
                                    @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->kode_barang }} — {{ $product->nama_barang }} (Stok: {{ $product->stok }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-24">
                                <input type="number" :name="'items[' + index + '][qty]'" x-model="item.qty" min="1" class="form-input text-sm" placeholder="Qty" required>
                            </div>
                            <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="p-2 text-red-400 hover:bg-red-500/10 rounded-lg transition-colors">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </template>
                </div>
                @error('items') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t dark:border-white/5 border-gray-100">
                <a href="{{ route('borrowings.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="save" class="w-4 h-4"></i> Simpan Peminjaman
                </button>
            </div>
        </form>
    </div>

    <script>
        function borrowingForm() {
            return {
                items: [{ product_id: '', qty: 1 }],
                addItem() { this.items.push({ product_id: '', qty: 1 }); },
                removeItem(index) { this.items.splice(index, 1); }
            }
        }
    </script>
</x-app-layout>
