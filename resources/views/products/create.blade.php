<x-app-layout>
    <x-slot:title>Tambah Barang</x-slot:title>

    <div class="max-w-2xl mx-auto space-y-6 animate-fade-in-up">
        <div>
            <h2 class="text-2xl font-bold dark:text-white text-gray-900">Tambah Barang Baru</h2>
            <p class="text-sm dark:text-gray-400 text-gray-500 mt-1">Isi data barang inventaris yang akan ditambahkan</p>
        </div>

        <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" class="glass-card p-6 space-y-5">
            @csrf

            <!-- Kode & Nama -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-1">Kode Barang</label>
                    <input type="text" name="kode_barang" value="{{ old('kode_barang', $kodeBarang) }}" class="form-input font-mono" required>
                    @error('kode_barang') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-1">Nama Barang</label>
                    <input type="text" name="nama_barang" value="{{ old('nama_barang') }}" class="form-input" placeholder="Laptop Dell Latitude..." required>
                    @error('nama_barang') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Kategori -->
            <div>
                <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-1">Kategori</label>
                <select name="category_id" class="form-input" required>
                    <option value="">Pilih Kategori</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
                @error('category_id') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Stok & Min Stok -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-1">Stok</label>
                    <input type="number" name="stok" value="{{ old('stok', 0) }}" min="0" class="form-input" required>
                    @error('stok') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-1">Minimum Stok</label>
                    <input type="number" name="min_stok" value="{{ old('min_stok', 5) }}" min="0" class="form-input" required>
                    @error('min_stok') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Lokasi & Kondisi -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-1">Lokasi Penyimpanan</label>
                    <input type="text" name="lokasi" value="{{ old('lokasi') }}" class="form-input" placeholder="Gudang A - Rak 1">
                    @error('lokasi') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-1">Kondisi</label>
                    <select name="kondisi" class="form-input" required>
                        <option value="baik" {{ old('kondisi') === 'baik' ? 'selected' : '' }}>Baik</option>
                        <option value="rusak_ringan" {{ old('kondisi') === 'rusak_ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                        <option value="rusak_berat" {{ old('kondisi') === 'rusak_berat' ? 'selected' : '' }}>Rusak Berat</option>
                    </select>
                    @error('kondisi') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Deskripsi -->
            <div>
                <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-1">Deskripsi</label>
                <textarea name="deskripsi" rows="3" class="form-input" placeholder="Deskripsi barang...">{{ old('deskripsi') }}</textarea>
                @error('deskripsi') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Gambar -->
            <div x-data="{ preview: null }">
                <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-1">Gambar Barang</label>
                <div class="flex items-center gap-4">
                    <label class="flex-1 flex items-center justify-center h-32 border-2 border-dashed dark:border-white/10 border-gray-300 rounded-xl cursor-pointer hover:border-primary-500 transition-colors">
                        <div class="text-center" x-show="!preview">
                            <i data-lucide="upload-cloud" class="w-8 h-8 dark:text-gray-500 text-gray-400 mx-auto mb-2"></i>
                            <p class="text-sm dark:text-gray-400 text-gray-500">Klik untuk upload</p>
                            <p class="text-xs dark:text-gray-600 text-gray-400">JPG, PNG, WebP (Max 2MB)</p>
                        </div>
                        <img x-show="preview" :src="preview" class="h-full object-contain rounded-lg" style="display: none;">
                        <input type="file" name="gambar" accept="image/*" class="hidden"
                               @change="preview = URL.createObjectURL($event.target.files[0])">
                    </label>
                </div>
                @error('gambar') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t dark:border-white/5 border-gray-100">
                <a href="{{ route('products.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Simpan Barang
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
