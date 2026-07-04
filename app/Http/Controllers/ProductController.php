<?php

namespace App\Http\Controllers;

use App\BigData\KafkaSimulator;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(nama_barang) LIKE ?', ["%" . strtolower($search) . "%"])
                  ->orWhereRaw('LOWER(kode_barang) LIKE ?', ["%" . strtolower($search) . "%"])
                  ->orWhereRaw('LOWER(lokasi) LIKE ?', ["%" . strtolower($search) . "%"]);
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Filter by kondisi
        if ($request->filled('kondisi')) {
            $query->where('kondisi', $request->kondisi);
        }

        // Filter low stock
        if ($request->boolean('low_stock')) {
            $query->whereRaw('stok <= min_stok');
        }

        // Sort
        $sortBy = $request->get('sort', 'created_at');
        $sortDir = $request->get('dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $products = $query->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $kodeBarang = Product::generateKode();
        return view('products.create', compact('categories', 'kodeBarang'));
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
            'gambar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('gambar')) {
            $validated['gambar'] = $request->file('gambar')->store('products', 'public');
        }

        $product = Product::create($validated);

        // Produce Kafka event
        KafkaSimulator::onProductChange('created', $product->toArray(), $product->id);

        // Check low stock alert
        if ($product->isLowStock()) {
            KafkaSimulator::alertLowStock($product->toArray(), $product->id);
        }

        return redirect()->route('products.index')
            ->with('success', 'Barang berhasil ditambahkan!');
    }

    public function show(Product $product)
    {
        $product->load('category', 'borrowingDetails.borrowing');
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'kode_barang' => 'required|string|unique:products,kode_barang,' . $product->id,
            'nama_barang' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'stok' => 'required|integer|min:0',
            'min_stok' => 'required|integer|min:0',
            'lokasi' => 'nullable|string|max:255',
            'kondisi' => 'required|in:baik,rusak_ringan,rusak_berat',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('gambar')) {
            // Delete old image
            if ($product->gambar) {
                Storage::disk('public')->delete($product->gambar);
            }
            $validated['gambar'] = $request->file('gambar')->store('products', 'public');
        }

        $oldStok = $product->stok;
        $product->update($validated);

        // Produce Kafka event
        KafkaSimulator::onProductChange('updated', array_merge($product->toArray(), [
            'old_stok' => $oldStok,
            'stok_change' => $product->stok - $oldStok,
        ]), $product->id);

        // Check low stock alert
        if ($product->isLowStock()) {
            KafkaSimulator::alertLowStock($product->toArray(), $product->id);
        }

        return redirect()->route('products.index')
            ->with('success', 'Barang berhasil diperbarui!');
    }

    public function destroy(Product $product)
    {
        $productData = $product->toArray();

        // Delete image
        if ($product->gambar) {
            Storage::disk('public')->delete($product->gambar);
        }

        $product->delete(); // Soft delete

        // Produce Kafka event
        KafkaSimulator::onProductChange('deleted', $productData, $product->id);

        return redirect()->route('products.index')
            ->with('success', 'Barang berhasil dihapus!');
    }
}
