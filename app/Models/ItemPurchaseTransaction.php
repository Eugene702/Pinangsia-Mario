<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ItemPurchaseTransaction extends Model
{
    use HasUuids;
    protected $guarded = ['created_at', 'updated_at'];

    public function inventory(){
        return $this->belongsTo(Inventory::class, 'inventoryId', 'id');
    }
}
