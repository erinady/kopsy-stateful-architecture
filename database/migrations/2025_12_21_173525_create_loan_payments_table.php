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
        Schema::create('loan_payments', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->enum('status', ['dibayar', 'terlambat']);
            $table->enum('method', ['tunai', 'nontunai']);
            $table->string('attachment');
            $table->foreignId('loan_id')->nullable()->constrained('loans')->onDelete('cascade');
            $table->date('payment_date');
            $table->uuid('updated_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_payments');
    }
};
