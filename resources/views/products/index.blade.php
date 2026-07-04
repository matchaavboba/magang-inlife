<x-app-layout>
    <x-slot:title>Master Barang</x-slot:title>

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 animate-fade-in-up">
            <div>
                <h2 class="text-2xl font-bold dark:text-white text-gray-900">Master Barang</h2>
                <p class="text-sm dark:text-gray-400 text-gray-500 mt-1">Kelola data inventaris barang kantor</p>
            </div>
            @role('admin|staff')
            <a href="{{ route('products.create') }}" class="btn btn-primary">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Tambah Barang
            </a>
            @endrole
        </div>

        <!-- Filters -->
        <div class="glass-card p-4">
            <form method="GET" action="{{ route('products.index') }}" class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cari barang, kode, atau lokasi..."
                           class="form-input">
                </div>
                <select name="category" class="form-input sm:w-44">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                    @endforeach
                </select>
                <select name="kondisi" class="form-input sm:w-40">
                    <option value="">Semua Kondisi</option>
                    <option value="baik" {{ request('kondisi') === 'baik' ? 'selected' : '' }}>Baik</option>
                    <option value="rusak_ringan" {{ request('kondisi') === 'rusak_ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                    <option value="rusak_berat" {{ request('kondisi') === 'rusak_berat' ? 'selected' : '' }}>Rusak Berat</option>
                </select>
                <label class="flex items-center gap-2 cursor-pointer dark:text-gray-300 text-gray-600 text-sm whitespace-nowrap">
                    <input type="checkbox" name="low_stock" value="1" {{ request('low_stock') ? 'checked' : '' }}
                           class="rounded border-gray-300 dark:border-gray-600 text-primary-500 focus:ring-primary-500">
                    Stok Menipis
                </label>
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="search" class="w-4 h-4"></i>
                    Cari
                </button>
                @if(request()->hasAny(['search', 'category', 'kondisi', 'low_stock']))
                <a href="{{ route('products.index') }}" class="btn btn-secondary">Reset</a>
                @endif
            </form>
        </div>

        <!-- Products Table -->
        <div class="glass-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="w-12">#</th>
                            <th>
                                <a href="{{ route('products.index', array_merge(request()->query(), ['sort' => 'kode_barang', 'dir' => request('sort') === 'kode_barang' && request('dir') === 'asc' ? 'desc' : 'asc'])) }}"
                                   class="flex items-center gap-1 hover:text-primary-400">
                                    Kode
                                    @if(request('sort') === 'kode_barang')
                                    <i data-lucide="{{ request('dir') === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-3 h-3"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>
                                <a href="{{ route('products.index', array_merge(request()->query(), ['sort' => 'stok', 'dir' => request('sort') === 'stok' && request('dir') === 'asc' ? 'desc' : 'asc'])) }}"
                                   class="flex items-center gap-1 hover:text-primary-400">
                                    Stok
                                    @if(request('sort') === 'stok')
                                    <i data-lucide="{{ request('dir') === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="w-3 h-3"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Lokasi</th>
                            <th>Kondisi</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $i => $product)
                        <tr class="animate-fade-in-up" style="animation-delay: {{ $i * 0.03 }}s">
                            <td class="dark:text-gray-500 text-gray-400 text-xs">{{ $products->firstItem() + $i }}</td>
                            <td>
                                <span class="font-mono text-xs dark:text-primary-400 text-primary-600 font-medium">{{ $product->kode_barang }}</span>
                            </td>
                            <td>
                                <div class="flex items-center gap-3">
                                    @if($product->gambar)
                                    <img src="{{ Storage::url($product->gambar) }}" alt="{{ $product->nama_barang }}"
                                         class="w-9 h-9 rounded-lg object-cover border dark:border-white/10 border-gray-200">
                                    @else
                                    <div class="w-9 h-9 rounded-lg bg-primary-500/10 flex items-center justify-center">
                                        <i data-lucide="package" class="w-4 h-4 text-primary-400"></i>
                                    </div>
                                    @endif
                                    <div>
                                        <a href="{{ route('products.show', $product) }}" class="font-medium dark:text-white text-gray-900 hover:text-primary-500 transition-colors">
                                            {{ $product->nama_barang }}
                                        </a>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-purple">{{ $product->category->name ?? '-' }}</span>
                            </td>
                            <td>
                                @if($product->stok === 0)
                                <span class="text-red-400 font-bold">0</span>
                                @elseif($product->isLowStock())
                                <span class="text-amber-400 font-bold">{{ $product->stok }}</span>
                                <span class="text-[10px] dark:text-gray-500 text-gray-400"> / {{ $product->min_stok }}</span>
                                @else
                                <span class="dark:text-gray-300 text-gray-700 font-medium">{{ $product->stok }}</span>
                                @endif
                            </td>
                            <td class="text-xs dark:text-gray-400 text-gray-500">{{ $product->lokasi ?? '-' }}</td>
                            <td>
                                @php
                                    $kondisiBadge = match($product->kondisi) {
                                        'baik' => 'badge-success',
                                        'rusak_ringan' => 'badge-warning',
                                        'rusak_berat' => 'badge-danger',
                                        default => 'badge-info',
                                    };
                                @endphp
                                <span class="badge {{ $kondisiBadge }}">{{ ucfirst(str_replace('_', ' ', $product->kondisi)) }}</span>
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('products.show', $product) }}"
                                       class="p-1.5 rounded-lg hover:bg-primary-500/10 text-primary-400 transition-colors"
                                       title="Detail">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                    @role('admin|staff')
                                    <a href="{{ route('products.edit', $product) }}"
                                       class="p-1.5 rounded-lg hover:bg-amber-500/10 text-amber-400 transition-colors"
                                       title="Edit">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </a>
                                    <form method="POST" action="{{ route('products.destroy', $product) }}"
                                          onsubmit="return confirm('Hapus barang {{ $product->nama_barang }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="p-1.5 rounded-lg hover:bg-red-500/10 text-red-400 transition-colors"
                                                title="Hapus">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                    @endrole
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-12">
                                <i data-lucide="package" class="w-12 h-12 dark:text-gray-600 text-gray-300 mx-auto mb-3"></i>
                                <p class="text-sm dark:text-gray-400 text-gray-500">Belum ada data barang.</p>
                                @role('admin|staff')
                                <a href="{{ route('products.create') }}" class="btn btn-primary mt-3 inline-flex">
                                    <i data-lucide="plus" class="w-4 h-4"></i> Tambah Barang
                                </a>
                                @endrole
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($products->hasPages())
            <div class="px-6 py-4 border-t dark:border-white/5 border-gray-100">
                {{ $products->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
