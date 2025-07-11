<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'quantity',
        'minimum_stock',
        'notes',
        'status', // tambahkan status
    ];

    protected $casts = [
        'status' => 'string', // bisa enum jika perlu
    ];

    // Relasi dengan peminjaman
    public function borrowings()
    {
        return $this->hasMany(InventoryBorrowing::class);
    }
}
