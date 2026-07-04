<x-app-layout>
    <x-slot:title>Edit Profile</x-slot:title>

    <div class="max-w-2xl mx-auto space-y-6 animate-fade-in-up">
        <div>
            <h2 class="text-2xl font-bold dark:text-white text-gray-900">Edit Profile</h2>
            <p class="text-sm dark:text-gray-400 text-gray-500 mt-1">Perbarui informasi profil akun Anda</p>
        </div>

        <form method="POST" action="{{ route('profile.update') }}" class="glass-card p-6 space-y-4">
            @csrf @method('PATCH')

            <!-- Name -->
            <div>
                <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-1">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-input" required autocomplete="name">
                @error('name') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Email -->
            <div>
                <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-input" required autocomplete="username">
                @error('email') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Role (Read Only) -->
            <div>
                <label class="block text-sm font-medium dark:text-gray-300 text-gray-700 mb-1">Role Akses</label>
                <input type="text" value="{{ ucfirst($user->roles->first()->name ?? 'user') }}" class="form-input opacity-70" readonly disabled>
                <p class="text-[11px] dark:text-gray-500 text-gray-400 mt-1">Role Anda ditentukan oleh administrator dan tidak dapat diubah sendiri.</p>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t dark:border-white/5 border-gray-100">
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="save" class="w-4 h-4"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
