<?php

namespace App\Observers;

use App\Models\Inventory;
use App\Models\ItemPurchaseTransaction;

class ItemPurchaseTransactionObserve
{
    /**
     * Handle the ItemPurchaseTransaction "created" event.
     */
    public function created(ItemPurchaseTransaction $itemPurchaseTransaction): void
    {
        $inventory = Inventory::find($itemPurchaseTransaction->inventoryId);
        if($inventory){
            $inventory->quantity += $itemPurchaseTransaction->qty;
            $inventory->save();
        }
    }

    /**
     * Handle the ItemPurchaseTransaction "updated" event.
     */
    public function updated(ItemPurchaseTransaction $itemPurchaseTransaction): void
    {
        $inventory = Inventory::find($itemPurchaseTransaction->inventoryId);
        if ($inventory) {
            $inventory->quantity += $itemPurchaseTransaction->qty - $itemPurchaseTransaction->getOriginal('qty');
            $inventory->save();
        }
    }

    /**
     * Handle the ItemPurchaseTransaction "deleted" event.
     */
    public function deleted(ItemPurchaseTransaction $itemPurchaseTransaction): void
    {
        $inventory = Inventory::find($itemPurchaseTransaction->inventoryId);
        if ($inventory) {
            $inventory->quantity -= $itemPurchaseTransaction->qty;
            $inventory->save();
        }
    }

    /**
     * Handle the ItemPurchaseTransaction "restored" event.
     */
    public function restored(ItemPurchaseTransaction $itemPurchaseTransaction): void
    {
        //
    }

    /**
     * Handle the ItemPurchaseTransaction "force deleted" event.
     */
    public function forceDeleted(ItemPurchaseTransaction $itemPurchaseTransaction): void
    {
        //
    }
}
