<x-app-layout>
    <x-slot:title>Kategori</x-slot:title>

    <div class="space-y-6" x-data="{ showModal: false, editId: null, editName: '', editDesc: '' }">
        <div class="flex items-center justify-between animate-fade-in-up">
            <div>
                <h2 class="text-2xl font-bold dark:text-white text-gray-900">Kategori Barang</h2>
                <p class="text-sm dark:text-gray-400 text-gray-500 mt-1">Kelola kategori inventaris</p>
            </div>
            <button @click="showModal = true; editId = null; editName = ''; editDesc = ''" class="btn btn-primary">
                <i data-lucide="plus" class="w-4 h-4"></i> Tambah Kategori
            </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 stagger-children">
            @foreach($categories as $cat)
            <div class="glass-card p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-500/10 flex items-center justify-center">
                        <i data-lucide="tag" class="w-5 h-5 text-purple-400"></i>
                    </div>
                    <div class="flex gap-1">
                        <button @click="showModal = true; editId = {{ $cat->id }}; editName = '{{ $cat->name }}'; editDesc = '{{ $cat->description }}'"
                                class="p-1.5 rounded-lg hover:bg-amber-500/10 text-amber-400 transition-colors">
                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                        </button>
                        @if($cat->products_count === 0)
                        <form method="POST" action="{{ route('categories.destroy', $cat) }}" onsubmit="return confirm('Hapus kategori {{ $cat->name }}?')">
                            @csrf @method('DELETE')
                            <button class="p-1.5 rounded-lg hover:bg-red-500/10 text-red-400 transition-colors">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
                <h3 class="text-sm font-semibold dark:text-white text-gray-900">{{ $cat->name }}</h3>
                <p class="text-xs dark:text-gray-400 text-gray-500 mt-1">{{ $cat->description ?? '-' }}</p>
                <div class="mt-3 flex items-center gap-1 text-xs dark:text-gray-500 text-gray-400">
                    <i data-lucide="package" class="w-3 h-3"></i>
                    {{ $cat->products_count }} barang
                </div>
            </div>
            @endforeach
        </div>

        <!-- Modal -->
        <div x-show="showModal" class="modal-backdrop" @click.self="showModal = false" style="display: none;">
            <div class="modal-content p-6">
                <h3 class="text-lg font-semibold dark:text-white text-gray-900 mb-4" x-text="editId ? 'Edit Kategori' : 'Tambah Kategori'"></h3>
                <form :action="editId ? '{{ url('categories') }}/' + editId : '{{ route('categories.store') }}'" method="POST" class="space-y-4">
                    @csrf
                    <template x-if="editId"><input type="hidden" name="_method" value="PUT"></template>
                    <div>
                        <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-1">Nama</label>
                        <input type="text" name="name" x-model="editName" class="form-input" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-1">Deskripsi</label>
                        <textarea name="description" x-model="editDesc" rows="2" class="form-input"></textarea>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showModal = false" class="btn btn-secondary">Batal</button>
                        <button type="submit" class="btn btn-primary"><i data-lucide="save" class="w-4 h-4"></i> Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
