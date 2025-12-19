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
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->decimal('subtotal', 15, 2);
            $table->decimal('item_price', 15, 2);
            $table->decimal('item_profit', 15, 2);
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('invoice_number')->constrained('invoices')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
