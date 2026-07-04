<!DOCTYPE html>
<html lang="id" x-data x-init="$store.darkMode.init()" :class="{ 'dark': $store.darkMode.on }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Sistem Manajemen Inventaris PT Telkomsel — Kelola inventaris kantor secara efisien">

    <title>{{ $title ?? 'Dashboard' }} — Inventaris Telkomsel</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 dark:bg-surface-900 transition-colors duration-300">

    <!-- Toast Notification -->
    <div x-show="$store.toast.show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-x-4"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         :class="$store.toast.type === 'success' ? 'toast-success' : 'toast-error'"
         class="toast"
         style="display: none;">
        <span x-text="$store.toast.message"></span>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
    <script>
        document.addEventListener('alpine:init', () => {
            setTimeout(() => Alpine.store('toast').fire('{{ session('success') }}', 'success'), 300);
        });
    </script>
    @endif
    @if(session('error'))
    <script>
        document.addEventListener('alpine:init', () => {
            setTimeout(() => Alpine.store('toast').fire('{{ session('error') }}', 'error', 5000), 300);
        });
    </script>
    @endif

    <div class="flex min-h-screen">
        <!-- ===== Sidebar ===== -->
        <aside class="sidebar fixed top-0 left-0 h-screen dark:bg-surface-800 bg-white border-r dark:border-white/5 border-gray-200 flex flex-col z-30"
               :class="$store.sidebar.open ? '' : '-translate-x-full lg:translate-x-0'"
               x-transition>

            <!-- Logo -->
            <div class="p-6 border-b dark:border-white/5 border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-sm font-bold dark:text-white text-gray-900">Inventaris</h1>
                        <p class="text-xs dark:text-gray-400 text-gray-500">PT Telkomsel</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
                <p class="text-xs font-semibold uppercase tracking-wider dark:text-gray-500 text-gray-400 px-3 mb-3">Menu</p>

                <a href="{{ route('dashboard') }}"
                   class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                   @click="$store.sidebar.close()">
                    <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                    Dashboard
                </a>

                @role('admin|staff')
                <a href="{{ route('products.index') }}"
                   class="sidebar-link {{ request()->routeIs('products.*') ? 'active' : '' }}"
                   @click="$store.sidebar.close()">
                    <i data-lucide="package" class="w-5 h-5"></i>
                    Master Barang
                </a>

                <a href="{{ route('categories.index') }}"
                   class="sidebar-link {{ request()->routeIs('categories.*') ? 'active' : '' }}"
                   @click="$store.sidebar.close()">
                    <i data-lucide="tags" class="w-5 h-5"></i>
                    Kategori
                </a>
                @endrole

                @role('admin|staff')
                <a href="{{ route('borrowings.index') }}"
                   class="sidebar-link {{ request()->routeIs('borrowings.*') ? 'active' : '' }}"
                   @click="$store.sidebar.close()">
                    <i data-lucide="arrow-left-right" class="w-5 h-5"></i>
                    Peminjaman
                </a>
                @endrole

                <p class="text-xs font-semibold uppercase tracking-wider dark:text-gray-500 text-gray-400 px-3 mt-6 mb-3">Analytics</p>

                <a href="{{ route('bigdata.index') }}"
                   class="sidebar-link {{ request()->routeIs('bigdata.*') ? 'active' : '' }}"
                   @click="$store.sidebar.close()">
                    <i data-lucide="database" class="w-5 h-5"></i>
                    Big Data
                    <span class="ml-auto text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-primary-500/20 text-primary-400">LIVE</span>
                </a>

                @role('admin|manager')
                <a href="{{ route('reports.index') }}"
                   class="sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}"
                   @click="$store.sidebar.close()">
                    <i data-lucide="file-bar-chart" class="w-5 h-5"></i>
                    Laporan
                </a>
                @endrole
            </nav>

            <!-- User Info -->
            <div class="p-4 border-t dark:border-white/5 border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white font-semibold text-sm">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium dark:text-white text-gray-900 truncate">{{ Auth::user()->name }}</p>
                        <p class="text-xs dark:text-gray-400 text-gray-500 truncate">
                            <span class="badge badge-info text-[10px] px-1.5 py-0">{{ ucfirst(Auth::user()->roles->first()->name ?? 'user') }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Mobile Sidebar Overlay -->
        <div x-show="$store.sidebar.open"
             @click="$store.sidebar.toggle()"
             class="fixed inset-0 bg-black/50 z-20 lg:hidden"
             x-transition:enter="transition-opacity ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             style="display: none;"></div>

        <!-- ===== Main Content ===== -->
        <main class="flex-1 lg:ml-[260px]">
            <!-- Top Bar -->
            <header class="sticky top-0 z-10 dark:bg-surface-800/80 bg-white/80 backdrop-blur-xl border-b dark:border-white/5 border-gray-200">
                <div class="flex items-center justify-between px-6 py-3">
                    <div class="flex items-center gap-4">
                        <!-- Mobile menu toggle -->
                        <button @click="$store.sidebar.toggle()" class="lg:hidden dark:text-gray-400 text-gray-500 hover:text-primary-500 transition-colors">
                            <i data-lucide="menu" class="w-6 h-6"></i>
                        </button>

                        <!-- Breadcrumb -->
                        <nav class="hidden sm:flex items-center gap-2 text-sm">
                            <span class="dark:text-gray-500 text-gray-400">
                                <i data-lucide="home" class="w-4 h-4"></i>
                            </span>
                            @if(isset($breadcrumbs))
                                @foreach($breadcrumbs as $crumb)
                                    <span class="dark:text-gray-600 text-gray-300">/</span>
                                    @if(isset($crumb['url']))
                                        <a href="{{ $crumb['url'] }}" class="dark:text-gray-400 text-gray-500 hover:text-primary-500 transition-colors">{{ $crumb['label'] }}</a>
                                    @else
                                        <span class="dark:text-gray-300 text-gray-700 font-medium">{{ $crumb['label'] }}</span>
                                    @endif
                                @endforeach
                            @else
                                <span class="dark:text-gray-600 text-gray-300">/</span>
                                <span class="dark:text-gray-300 text-gray-700 font-medium">{{ $title ?? 'Dashboard' }}</span>
                            @endif
                        </nav>
                    </div>

                    <div class="flex items-center gap-3">
                        <!-- Low Stock Notification -->
                        @php
                            $lowStockCount = \App\Models\Product::whereRaw('stok <= min_stok')->count();
                        @endphp
                        @if($lowStockCount > 0)
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="relative p-2 rounded-lg dark:text-gray-400 text-gray-500 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors">
                                <i data-lucide="bell" class="w-5 h-5"></i>
                                <span class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-red-500 rounded-full text-[10px] text-white flex items-center justify-center font-bold animate-pulse">{{ $lowStockCount }}</span>
                            </button>
                            <div x-show="open" @click.outside="open = false"
                                 x-transition
                                 class="absolute right-0 top-full mt-2 w-72 dark:bg-surface-800 bg-white rounded-xl shadow-xl border dark:border-white/10 border-gray-200 p-3"
                                 style="display: none;">
                                <p class="text-sm font-semibold dark:text-white text-gray-900 mb-2">⚠️ Stok Menipis</p>
                                <p class="text-xs dark:text-gray-400 text-gray-500">{{ $lowStockCount }} barang memiliki stok di bawah minimum.</p>
                                <a href="{{ route('products.index', ['low_stock' => 1]) }}" class="mt-2 block text-xs text-primary-500 hover:text-primary-400 font-medium">Lihat semua →</a>
                            </div>
                        </div>
                        @endif

                        <!-- Dark Mode Toggle -->
                        <button @click="$store.darkMode.toggle()"
                                class="p-2 rounded-lg dark:text-gray-400 text-gray-500 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors"
                                :title="$store.darkMode.on ? 'Light Mode' : 'Dark Mode'">
                            <i x-show="$store.darkMode.on" data-lucide="sun" class="w-5 h-5"></i>
                            <i x-show="!$store.darkMode.on" data-lucide="moon" class="w-5 h-5"></i>
                        </button>

                        <!-- Profile Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 transition-colors">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white font-semibold text-xs">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </div>
                                <i data-lucide="chevron-down" class="w-4 h-4 dark:text-gray-400 text-gray-500"></i>
                            </button>
                            <div x-show="open" @click.outside="open = false"
                                 x-transition
                                 class="absolute right-0 top-full mt-2 w-48 dark:bg-surface-800 bg-white rounded-xl shadow-xl border dark:border-white/10 border-gray-200 py-1.5"
                                 style="display: none;">
                                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-3 py-2 text-sm dark:text-gray-300 text-gray-600 hover:bg-gray-100 dark:hover:bg-white/5">
                                    <i data-lucide="user" class="w-4 h-4"></i> Profile
                                </a>
                                <hr class="my-1 dark:border-white/5 border-gray-100">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 text-sm text-red-500 hover:bg-gray-100 dark:hover:bg-white/5">
                                        <i data-lucide="log-out" class="w-4 h-4"></i> Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="p-6">
                {{ $slot }}
            </div>
        </main>
    </div>

    <script>
        // Initialize Lucide icons
        document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
        // Re-init after Alpine updates (safely avoiding infinite loop)
        document.addEventListener('alpine:initialized', () => {
            const observer = new MutationObserver(() => {
                if (document.querySelector('i[data-lucide]')) {
                    lucide.createIcons();
                }
            });
            observer.observe(document.body, { childList: true, subtree: true });
        });
    </script>
</body>
</html>
