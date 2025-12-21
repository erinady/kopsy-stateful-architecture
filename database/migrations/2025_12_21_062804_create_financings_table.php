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
        Schema::create('financings', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('product_type');
            $table->string('brand');
            $table->string('color');
            $table->enum('condition', ['bekas', 'baru']);
            $table->text('description');
            $table->decimal('price', 15, 2);
            $table->integer('qty');
            $table->decimal('profit', 15, 2);
            $table->enum('status', ['belum ditinjau', 'disetujui', 'disetujui dengan catatan', 'ditolak', 'menunggu kelengkapan dokumen', 'barang diterima']);
            $table->foreignUuid('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financings');
    }
};
