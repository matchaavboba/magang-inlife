<?php

namespace App\Http\Controllers;

use App\Models\Borrowing;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductsExport;
use App\Exports\BorrowingsExport;

class ReportController extends Controller
{
    /**
     * Reports overview page
     */
    public function index()
    {
        return view('reports.index');
    }

    /**
     * Export products to PDF
     */
    public function exportProductsPdf(Request $request)
    {
        $products = Product::with('category')->orderBy('kode_barang')->get();

        $pdf = Pdf::loadView('reports.products-pdf', [
            'products' => $products,
            'generated_at' => now()->format('d M Y H:i'),
            'title' => 'Laporan Inventaris Barang',
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('laporan-inventaris-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export products to Excel
     */
    public function exportProductsExcel()
    {
        return Excel::download(new ProductsExport, 'inventaris-' . now()->format('Y-m-d') . '.xlsx');
    }

    /**
     * Export borrowings to PDF
     */
    public function exportBorrowingsPdf(Request $request)
    {
        $borrowings = Borrowing::with(['details.product', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        $pdf = Pdf::loadView('reports.borrowings-pdf', [
            'borrowings' => $borrowings,
            'generated_at' => now()->format('d M Y H:i'),
            'title' => 'Laporan Peminjaman Barang',
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('laporan-peminjaman-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export borrowings to Excel
     */
    public function exportBorrowingsExcel()
    {
        return Excel::download(new BorrowingsExport, 'peminjaman-' . now()->format('Y-m-d') . '.xlsx');
    }
}
