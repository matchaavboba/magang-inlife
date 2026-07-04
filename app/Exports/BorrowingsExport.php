<?php

namespace App\Exports;

use App\Models\Borrowing;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BorrowingsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return Borrowing::with(['details.product', 'user'])->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return ['ID', 'Peminjam', 'Barang', 'Tgl Pinjam', 'Tgl Kembali', 'Tgl Dikembalikan', 'Status', 'Dicatat Oleh'];
    }

    public function map($borrowing): array
    {
        $items = $borrowing->details->map(fn($d) => $d->product->nama_barang . ' (x' . $d->qty . ')')->join(', ');

        return [
            $borrowing->id,
            $borrowing->borrower_name,
            $items,
            $borrowing->tanggal_pinjam->format('d/m/Y'),
            $borrowing->tanggal_kembali?->format('d/m/Y') ?? '-',
            $borrowing->tanggal_dikembalikan?->format('d/m/Y') ?? '-',
            ucfirst($borrowing->status),
            $borrowing->user->name ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
