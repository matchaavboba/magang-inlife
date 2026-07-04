<?php

namespace App\Http\Controllers\Api;

use App\BigData\KafkaSimulator;
use App\Http\Controllers\Controller;
use App\Models\Borrowing;
use App\Models\BorrowingDetail;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BorrowingApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Borrowing::with(['user', 'details.product']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->paginate(15));
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
                'user_id' => $request->user()->id,
                'borrower_name' => $validated['borrower_name'],
                'tanggal_pinjam' => $validated['tanggal_pinjam'],
                'tanggal_kembali' => $validated['tanggal_kembali'] ?? null,
                'status' => 'dipinjam',
                'catatan' => $validated['catatan'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);

                if ($product->stok < $item['qty']) {
                    throw new \Exception("Stok {$product->nama_barang} tidak mencukupi!");
                }

                BorrowingDetail::create([
                    'borrowing_id' => $borrowing->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'kondisi_pinjam' => $product->kondisi,
                ]);

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
            }

            // Kafka: Borrowing created event
            KafkaSimulator::onBorrowingChange('created', $borrowing->toArray(), $borrowing->id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Peminjaman berhasil dicatat.',
                'data' => $borrowing->load('details.product')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function show($id)
    {
        $borrowing = Borrowing::with(['user', 'details.product'])->find($id);

        if (!$borrowing) {
            return response()->json(['error' => 'Peminjaman tidak ditemukan.'], 404);
        }

        return response()->json($borrowing);
    }

    public function returnItems(Request $request, $id)
    {
        $borrowing = Borrowing::find($id);

        if (!$borrowing) {
            return response()->json(['error' => 'Peminjaman tidak ditemukan.'], 404);
        }

        if ($borrowing->status !== 'dipinjam') {
            return response()->json(['error' => 'Peminjaman ini sudah dikembalikan.'], 400);
        }

        DB::beginTransaction();

        try {
            foreach ($borrowing->details as $detail) {
                $detail->product->increment('stok', $detail->qty);
                $detail->update(['kondisi_kembali' => 'baik']);

                // Kafka: Stock updated
                KafkaSimulator::produce(
                    KafkaSimulator::TOPIC_INVENTORY,
                    KafkaSimulator::EVENT_STOCK_UPDATED,
                    [
                        'product_id' => $detail->product_id,
                        'nama_barang' => $detail->product->nama_barang,
                        'change' => $detail->qty,
                        'reason' => 'return',
                    ],
                    'product',
                    $detail->product_id
                );
            }

            $isLate = $borrowing->tanggal_kembali && $borrowing->tanggal_kembali->isPast();

            $borrowing->update([
                'status' => $isLate ? 'terlambat' : 'dikembalikan',
                'tanggal_dikembalikan' => now(),
            ]);

            // Kafka: Borrowing returned
            KafkaSimulator::onBorrowingChange('returned', $borrowing->fresh()->toArray(), $borrowing->id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengembalian barang berhasil dicatat.',
                'data' => $borrowing->load('details.product')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
