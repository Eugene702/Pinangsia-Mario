<?php

namespace App\Providers;

use App\Models\ItemPurchaseTransaction;
use App\Observers\ItemPurchaseTransactionObserve;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        ItemPurchaseTransaction::observe(ItemPurchaseTransactionObserve::class);
    }
}
