<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    // Tabel Induk Bundling
    Schema::create('bundlings', function (Blueprint $table) {
        $table->id();
        $table->string('bundle_code')->unique();
        $table->string('name');
        $table->decimal('bundle_price', 12, 2);
        $table->timestamps();
    });

    // Tabel Detail Komponen Bundling
    Schema::create('bundling_details', function (Blueprint $table) {
        $table->id();
        $table->foreignId('bundling_id')->constrained('bundlings')->onDelete('cascade');
        $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
        $table->integer('qty');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bundlings_and_details');
    }
};
