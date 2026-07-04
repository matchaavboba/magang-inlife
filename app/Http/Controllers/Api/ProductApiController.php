<?php

namespace App\Http\Controllers\Api;

use App\BigData\KafkaSimulator;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(nama_barang) LIKE ?', ["%" . strtolower($search) . "%"])
                  ->orWhereRaw('LOWER(kode_barang) LIKE ?', ["%" . strtolower($search) . "%"]);
            });
        }

        return response()->json($query->paginate(15));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_barang' => 'required|string|unique:products,kode_barang',
            'nama_barang' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'stok' => 'required|integer|min:0',
            'min_stok' => 'required|integer|min:0',
            'lokasi' => 'nullable|string|max:255',
            'kondisi' => 'required|in:baik,rusak_ringan,rusak_berat',
            'deskripsi' => 'nullable|string',
        ]);

        $product = Product::create($validated);

        // Produce Kafka event
        KafkaSimulator::onProductChange('created', $product->toArray(), $product->id);

        if ($product->isLowStock()) {
            KafkaSimulator::alertLowStock($product->toArray(), $product->id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dibuat.',
            'data' => $product
        ], 201);
    }

    public function show($id)
    {
        $product = Product::with('category')->find($id);

        if (!$product) {
            return response()->json(['error' => 'Produk tidak ditemukan.'], 404);
        }

        return response()->json($product);
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['error' => 'Produk tidak ditemukan.'], 404);
        }

        $validated = $request->validate([
            'kode_barang' => 'required|string|unique:products,kode_barang,' . $product->id,
            'nama_barang' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'stok' => 'required|integer|min:0',
            'min_stok' => 'required|integer|min:0',
            'lokasi' => 'nullable|string|max:255',
            'kondisi' => 'required|in:baik,rusak_ringan,rusak_berat',
            'deskripsi' => 'nullable|string',
        ]);

        $oldStok = $product->stok;
        $product->update($validated);

        // Produce Kafka event
        KafkaSimulator::onProductChange('updated', array_merge($product->toArray(), [
            'old_stok' => $oldStok,
            'stok_change' => $product->stok - $oldStok,
        ]), $product->id);

        if ($product->isLowStock()) {
            KafkaSimulator::alertLowStock($product->toArray(), $product->id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil diupdate.',
            'data' => $product
        ]);
    }

    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['error' => 'Produk tidak ditemukan.'], 404);
        }

        $productData = $product->toArray();
        $product->delete();

        // Produce Kafka event
        KafkaSimulator::onProductChange('deleted', $productData, $id);

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dihapus.'
        ]);
    }
}
