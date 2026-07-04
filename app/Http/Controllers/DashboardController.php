<?php

namespace App\Http\Controllers;

use App\BigData\KafkaSimulator;
use App\Models\Borrowing;
use App\Models\EventLog;
use App\Models\Product;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Stats
        $totalBarang = Product::count();
        $totalStok = Product::sum('stok');
        $barangDipinjam = Borrowing::where('status', 'dipinjam')->count();
        $barangTersedia = Product::where('stok', '>', 0)->count();
        $stokMenipis = Product::whereRaw('stok <= min_stok')->where('stok', '>', 0)->count();
        $stokHabis = Product::where('stok', 0)->count();

        // Low stock products
        $lowStockProducts = Product::whereRaw('stok <= min_stok')
            ->with('category')
            ->orderBy('stok', 'asc')
            ->limit(5)
            ->get();

        // Monthly borrowing chart data (last 12 months)
        $chartData = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $chartData[] = [
                'label' => $date->format('M'),
                'count' => Borrowing::whereYear('tanggal_pinjam', $date->year)
                    ->whereMonth('tanggal_pinjam', $date->month)
                    ->count(),
            ];
        }

        // Recent activity (from event logs)
        $recentActivity = EventLog::orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Overdue borrowings
        $overdueBorrowings = Borrowing::where('status', 'dipinjam')
            ->whereNotNull('tanggal_kembali')
            ->where('tanggal_kembali', '<', now())
            ->with('details.product')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'totalBarang',
            'totalStok',
            'barangDipinjam',
            'barangTersedia',
            'stokMenipis',
            'stokHabis',
            'lowStockProducts',
            'chartData',
            'recentActivity',
            'overdueBorrowings'
        ));
    }
}
