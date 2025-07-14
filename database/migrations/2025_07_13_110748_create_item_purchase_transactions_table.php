<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('item_purchase_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->bigInteger('inventoryId')->unsigned();
            $table->string('supplier');
            $table->integer('qty');
            $table->bigInteger('unitPrice')->unsigned();
            $table->longText('note')->nullable();
            $table->timestamps();
            
            $table->foreign('inventoryId')->references('id')->on('inventories')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_purchase_transactions');
    }
};
