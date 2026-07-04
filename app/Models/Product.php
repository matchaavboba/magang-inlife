<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'category_id',
        'stok',
        'min_stok',
        'lokasi',
        'kondisi',
        'gambar',
        'deskripsi',
    ];

    protected function casts(): array
    {
        return [
            'stok' => 'integer',
            'min_stok' => 'integer',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function borrowingDetails()
    {
        return $this->hasMany(BorrowingDetail::class);
    }

    public function eventLogs()
    {
        return $this->hasMany(EventLog::class, 'reference_id')
            ->where('reference_type', 'product');
    }

    /**
     * Check if stock is below minimum threshold
     */
    public function isLowStock(): bool
    {
        return $this->stok <= $this->min_stok;
    }

    /**
     * Get the available stock (total - borrowed)
     */
    public function getAvailableStockAttribute(): int
    {
        $borrowed = $this->borrowingDetails()
            ->whereHas('borrowing', fn($q) => $q->where('status', 'dipinjam'))
            ->sum('qty');

        return max(0, $this->stok - $borrowed);
    }

    /**
     * Generate unique kode_barang
     */
    public static function generateKode(string $prefix = 'BRG'): string
    {
        $last = static::withTrashed()
            ->where('kode_barang', 'LIKE', $prefix . '%')
            ->orderBy('kode_barang', 'desc')
            ->first();

        if (!$last) {
            return $prefix . '-0001';
        }

        $number = (int) substr($last->kode_barang, strlen($prefix) + 1);
        return $prefix . '-' . str_pad($number + 1, 4, '0', STR_PAD_LEFT);
    }
}
