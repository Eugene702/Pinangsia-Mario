<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryBorrowing extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_id',
        'user_id', // housekeeping yang meminjam
        'borrowed_quantity',
        'borrowed_at',
        'returned_at',
        'status', // dipinjam, dikembalikan, rusak, dll
        'notes',
    ];

    protected $casts = [
        'borrowed_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    // Relasi dengan inventory
    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    // Relasi dengan user (housekeeping)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        static::created(function ($borrowing) {
            $inventory = $borrowing->inventory;
            $inventory->decrement('quantity', $borrowing->borrowed_quantity);

            if ($inventory->quantity <= 0) {
                $inventory->update(['status' => 'unavailable']);
            }
        });
    }

    public function returnItem($returnStatus, $returnedQuantity, $notes = null)
    {
        $this->update([
            'status' => $returnStatus,
            'returned_at' => now(),
            'notes' => $notes ?? $this->notes,
        ]);

        $inventory = $this->inventory;

        // Jika status dikembalikan normal, tambahkan stok
        if ($returnStatus === 'returned') {
            $inventory->increment('quantity', $returnedQuantity);
        }

        // Update status inventory jika stok tersedia
        if ($inventory->quantity > 0) {
            $inventory->update(['status' => 'available']);
        }

        return $this;
    }

    public function scopeHistory($query)
    {
        return $query->with(['inventory', 'user'])
            ->orderBy('borrowed_at', 'desc');
    }
}
