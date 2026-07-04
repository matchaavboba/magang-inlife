<x-app-layout>
    <x-slot:title>Edit Barang</x-slot:title>

    <div class="max-w-2xl mx-auto space-y-6 animate-fade-in-up">
        <div>
            <h2 class="text-2xl font-bold dark:text-white text-gray-900">Edit Barang</h2>
            <p class="text-sm dark:text-gray-400 text-gray-500 mt-1">{{ $product->kode_barang }} — {{ $product->nama_barang }}</p>
        </div>

        <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data" class="glass-card p-6 space-y-5">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-1">Kode Barang</label>
                    <input type="text" name="kode_barang" value="{{ old('kode_barang', $product->kode_barang) }}" class="form-input font-mono" required>
                    @error('kode_barang') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-1">Nama Barang</label>
                    <input type="text" name="nama_barang" value="{{ old('nama_barang', $product->nama_barang) }}" class="form-input" required>
                    @error('nama_barang') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-1">Kategori</label>
                <select name="category_id" class="form-input" required>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
                @error('category_id') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-1">Stok</label>
                    <input type="number" name="stok" value="{{ old('stok', $product->stok) }}" min="0" class="form-input" required>
                    @error('stok') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-1">Minimum Stok</label>
                    <input type="number" name="min_stok" value="{{ old('min_stok', $product->min_stok) }}" min="0" class="form-input" required>
                    @error('min_stok') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-1">Lokasi Penyimpanan</label>
                    <input type="text" name="lokasi" value="{{ old('lokasi', $product->lokasi) }}" class="form-input">
                    @error('lokasi') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-1">Kondisi</label>
                    <select name="kondisi" class="form-input" required>
                        <option value="baik" {{ old('kondisi', $product->kondisi) === 'baik' ? 'selected' : '' }}>Baik</option>
                        <option value="rusak_ringan" {{ old('kondisi', $product->kondisi) === 'rusak_ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                        <option value="rusak_berat" {{ old('kondisi', $product->kondisi) === 'rusak_berat' ? 'selected' : '' }}>Rusak Berat</option>
                    </select>
                    @error('kondisi') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-1">Deskripsi</label>
                <textarea name="deskripsi" rows="3" class="form-input">{{ old('deskripsi', $product->deskripsi) }}</textarea>
            </div>

            <div x-data="{ preview: '{{ $product->gambar ? Storage::url($product->gambar) : '' }}' }">
                <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-1">Gambar Barang</label>
                <div class="flex items-center gap-4">
                    <label class="flex-1 flex items-center justify-center h-32 border-2 border-dashed dark:border-white/10 border-gray-300 rounded-xl cursor-pointer hover:border-primary-500 transition-colors">
                        <div class="text-center" x-show="!preview">
                            <i data-lucide="upload-cloud" class="w-8 h-8 dark:text-gray-500 text-gray-400 mx-auto mb-2"></i>
                            <p class="text-sm dark:text-gray-400 text-gray-500">Klik untuk upload</p>
                        </div>
                        <img x-show="preview" :src="preview" class="h-full object-contain rounded-lg">
                        <input type="file" name="gambar" accept="image/*" class="hidden"
                               @change="preview = URL.createObjectURL($event.target.files[0])">
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t dark:border-white/5 border-gray-100">
                <a href="{{ route('products.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Update Barang
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
