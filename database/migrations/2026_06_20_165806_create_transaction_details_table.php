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
    Schema::create('transaction_details', function (Blueprint $table) {
        $table->id();
        $table->foreignId('transaction_id')->constrained()->onDelete('cascade');
        
        // 1. Dibuat nullable agar bisa simpan transaksi bundling (solusi error utama)
        $table->foreignId('product_id')->nullable()->constrained()->onDelete('cascade'); 
        
        // 2. Gunakan unsignedBigInteger biasa tanpa constrained agar tidak error Foreign Key
        $table->unsignedBigInteger('bundling_id')->nullable(); 
        
        $table->integer('quantity');
        $table->decimal('price', 12, 2);
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_details');
    }
};
