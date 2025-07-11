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
        Schema::create('monthly_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('month'); // Format YYYY-MM-01
            $table->enum('shift_pattern', ['regular', 'custom'])->default('regular');
            $table->json('shift_data')->nullable(); // Untuk custom pattern
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'month']); // Satu staff satu jadwal per bulan
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_shifts');
    }
};
