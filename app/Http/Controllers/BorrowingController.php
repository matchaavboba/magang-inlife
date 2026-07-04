<?php

namespace App\Http\Controllers;

use App\BigData\KafkaSimulator;
use App\Models\Borrowing;
use App\Models\BorrowingDetail;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BorrowingController extends Controller
{
    public function index(Request $request)
    {
        $query = Borrowing::with(['user', 'details.product']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereRaw('LOWER(borrower_name) LIKE ?', ["%" . strtolower($search) . "%"]);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $borrowings = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return view('borrowings.index', compact('borrowings'));
    }

    public function create()
    {
        $products = Product::where('stok', '>', 0)
            ->with('category')
            ->orderBy('nama_barang')
            ->get();

        return view('borrowings.create', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'borrower_name' => 'required|string|max:255',
            'tanggal_pinjam' => 'required|date',
            'tanggal_kembali' => 'nullable|date|after_or_equal:tanggal_pinjam',
            'catatan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $borrowing = Borrowing::create([
                'user_id' => Auth::id(),
                'borrower_name' => $validated['borrower_name'],
                'tanggal_pinjam' => $validated['tanggal_pinjam'],
                'tanggal_kembali' => $validated['tanggal_kembali'] ?? null,
                'status' => 'dipinjam',
                'catatan' => $validated['catatan'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);

                // Check stock availability
                if ($product->stok < $item['qty']) {
                    throw new \Exception("Stok {$product->nama_barang} tidak mencukupi! (Tersedia: {$product->stok}, Diminta: {$item['qty']})");
                }

                BorrowingDetail::create([
                    'borrowing_id' => $borrowing->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'kondisi_pinjam' => $product->kondisi,
                ]);

                // Update stock (deduct)
                $product->decrement('stok', $item['qty']);

                // Kafka: Stock updated event
                KafkaSimulator::produce(
                    KafkaSimulator::TOPIC_INVENTORY,
                    KafkaSimulator::EVENT_STOCK_UPDATED,
                    [
                        'product_id' => $product->id,
                        'nama_barang' => $product->nama_barang,
                        'old_stok' => $product->stok + $item['qty'],
                        'new_stok' => $product->stok,
                        'change' => -$item['qty'],
                        'reason' => 'borrowing',
                    ],
                    'product',
                    $product->id
                );

                // Check low stock
                if ($product->fresh()->isLowStock()) {
                    KafkaSimulator::alertLowStock($product->fresh()->toArray(), $product->id);
                }
            }

            // Kafka: Borrowing created event
            KafkaSimulator::onBorrowingChange('created', array_merge(
                $borrowing->toArray(),
                ['items_count' => count($validated['items'])]
            ), $borrowing->id);

            DB::commit();

            return redirect()->route('borrowings.index')
                ->with('success', 'Peminjaman berhasil dibuat!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(Borrowing $borrowing)
    {
        $borrowing->load(['user', 'details.product.category']);
        return view('borrowings.show', compact('borrowing'));
    }

    public function returnItems(Request $request, Borrowing $borrowing)
    {
        if ($borrowing->status !== 'dipinjam') {
            return back()->with('error', 'Peminjaman ini sudah dikembalikan!');
        }

        $validated = $request->validate([
            'kondisi_kembali' => 'nullable|array',
            'kondisi_kembali.*' => 'in:baik,rusak_ringan,rusak_berat',
        ]);

        DB::beginTransaction();

        try {
            foreach ($borrowing->details as $detail) {
                // Update return condition on detail record
                $kondisi = $validated['kondisi_kembali'][$detail->id] ?? 'baik';
                $detail->update(['kondisi_kembali' => $kondisi]);

                // Restore stock and update condition only if product exists
                if ($detail->product) {
                    $detail->product->increment('stok', $detail->qty);

                    // If item was returned damaged, update product condition
                    if ($kondisi !== 'baik') {
                        $detail->product->update(['kondisi' => $kondisi]);
                    }
                }

                // Kafka: Stock updated
                KafkaSimulator::produce(
                    KafkaSimulator::TOPIC_INVENTORY,
                    KafkaSimulator::EVENT_STOCK_UPDATED,
                    [
                        'product_id' => $detail->product_id,
                        'nama_barang' => $detail->product ? $detail->product->nama_barang : 'Barang Terhapus',
                        'change' => $detail->qty,
                        'reason' => 'return',
                    ],
                    'product',
                    $detail->product_id
                );
            }

            // Determine if it's late
            $isLate = $borrowing->tanggal_kembali && $borrowing->tanggal_kembali->isPast();

            $borrowing->update([
                'status' => $isLate ? 'terlambat' : 'dikembalikan',
                'tanggal_dikembalikan' => now(),
            ]);

            // Kafka: Borrowing returned
            KafkaSimulator::onBorrowingChange('returned', $borrowing->fresh()->toArray(), $borrowing->id);

            DB::commit();

            return redirect()->route('borrowings.index')
                ->with('success', 'Barang berhasil dikembalikan!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
}
