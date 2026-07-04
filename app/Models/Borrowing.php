<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Borrowing extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'borrower_name',
        'tanggal_pinjam',
        'tanggal_kembali',
        'tanggal_dikembalikan',
        'status',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pinjam' => 'date',
            'tanggal_kembali' => 'date',
            'tanggal_dikembalikan' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(BorrowingDetail::class);
    }

    /**
     * Check if borrowing is overdue
     */
    public function isOverdue(): bool
    {
        return $this->status === 'dipinjam'
            && $this->tanggal_kembali
            && $this->tanggal_kembali->isPast();
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'dipinjam' => $this->isOverdue() ? 'badge-danger' : 'badge-warning',
            'dikembalikan' => 'badge-success',
            'terlambat' => 'badge-danger',
            default => 'badge-info',
        };
    }

    /**
     * Get display status
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'dipinjam' => $this->isOverdue() ? 'Terlambat' : 'Dipinjam',
            'dikembalikan' => 'Dikembalikan',
            'terlambat' => 'Terlambat',
            default => ucfirst($this->status),
        };
    }
}
