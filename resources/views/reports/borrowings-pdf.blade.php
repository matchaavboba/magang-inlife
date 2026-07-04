<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 11px;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .header {
            margin-bottom: 20px;
            border-bottom: 2px solid #e11d48;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            color: #e11d48;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 9px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>PT Telkomsel — Laporan Peminjaman Barang</h1>
        <p>Dicetak pada: {{ $generated_at }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Peminjam</th>
                <th>Barang & Qty</th>
                <th>Tgl Pinjam</th>
                <th>Tgl Kembali</th>
                <th>Tgl Dikembalikan</th>
                <th>Status</th>
                <th>Dicatat Oleh</th>
            </tr>
        </thead>
        <tbody>
            @foreach($borrowings as $borrowing)
            <tr>
                <td>{{ $borrowing->id }}</td>
                <td>{{ $borrowing->borrower_name }}</td>
                <td>
                    @foreach($borrowing->details as $detail)
                        {{ $detail->product->nama_barang ?? 'N/A' }} (x{{ $detail->qty }})<br>
                    @endforeach
                </td>
                <td>{{ $borrowing->tanggal_pinjam->format('d M Y') }}</td>
                <td>{{ $borrowing->tanggal_kembali?->format('d M Y') ?? '-' }}</td>
                <td>{{ $borrowing->tanggal_dikembalikan?->format('d M Y') ?? '-' }}</td>
                <td>{{ ucfirst($borrowing->status) }}</td>
                <td>{{ $borrowing->user->name ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Sistem Manajemen Inventaris PT Telkomsel
    </div>
</body>
</html>
