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
        <h1>PT Telkomsel — Laporan Inventaris Barang</h1>
        <p>Dicetak pada: {{ $generated_at }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama Barang</th>
                <th>Kategori</th>
                <th>Stok</th>
                <th>Min. Stok</th>
                <th>Lokasi</th>
                <th>Kondisi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
            <tr>
                <td><strong>{{ $product->kode_barang }}</strong></td>
                <td>{{ $product->nama_barang }}</td>
                <td>{{ $product->category->name ?? '-' }}</td>
                <td>{{ $product->stok }}</td>
                <td>{{ $product->min_stok }}</td>
                <td>{{ $product->lokasi ?? '-' }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $product->kondisi)) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Sistem Manajemen Inventaris PT Telkomsel
    </div>
</body>
</html>
