<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return Product::with('category')->orderBy('kode_barang')->get();
    }

    public function headings(): array
    {
        return ['Kode Barang', 'Nama Barang', 'Kategori', 'Stok', 'Min. Stok', 'Lokasi', 'Kondisi', 'Dibuat'];
    }

    public function map($product): array
    {
        return [
            $product->kode_barang,
            $product->nama_barang,
            $product->category->name ?? '-',
            $product->stok,
            $product->min_stok,
            $product->lokasi ?? '-',
            ucfirst(str_replace('_', ' ', $product->kondisi)),
            $product->created_at->format('d/m/Y'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
