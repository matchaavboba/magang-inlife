<!DOCTYPE html>
<html lang="id" x-data x-init="$store.darkMode.init()" :class="{ 'dark': $store.darkMode.on }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar — Sistem Manajemen Inventaris</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-surface-900 transition-colors duration-300 p-4">

    <div class="w-full max-w-md space-y-6 animate-fade-in-up">
        <!-- Logo Header -->
        <div class="text-center">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center mx-auto mb-4 shadow-lg shadow-primary-500/20">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <h1 class="text-xl font-bold dark:text-white text-gray-900">Sistem Inventaris Kantor</h1>
            <p class="text-xs dark:text-gray-400 text-gray-500 mt-1">Sistem Inventaris</p>
        </div>

        <!-- Register Card -->
        <div class="glass-card p-6">
            <h2 class="text-lg font-bold dark:text-white text-gray-900 mb-4">Buat Akun Baru</h2>
            
            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf

                <!-- Name -->
                <div>
                    <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-1">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-input" placeholder="Masukkan nama..." required autofocus autocomplete="name">
                    @error('name') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-input" placeholder="nama@email.com" required autocomplete="username">
                    @error('email') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-1">Password</label>
                    <input type="password" name="password" class="form-input" placeholder="••••••••" required autocomplete="new-password">
                    @error('password') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Confirm Password -->
                <div>
                    <label class="block text-xs font-semibold dark:text-gray-400 text-gray-600 uppercase tracking-wider mb-1">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" class="form-input" placeholder="••••••••" required autocomplete="new-password">
                </div>

                <!-- Submit -->
                <button type="submit" class="btn btn-primary w-full justify-center py-2.5">
                    Daftar
                </button>
            </form>
        </div>

        <div class="text-center text-xs dark:text-gray-500 text-gray-400">
            Sudah punya akun? <a href="{{ route('login') }}" class="text-primary-500 hover:underline">Masuk</a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
    </script>
</body>
</html>
