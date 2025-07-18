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
        Schema::create('monthly_shift_days', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->bigInteger('monthlyShiftId')->unsigned();
            $table->foreign('monthlyShiftId')->references('id')->on('monthly_shifts')->cascadeOnDelete()->cascadeOnUpdate();
            $table->integer('day');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_shift_days');
    }
};
