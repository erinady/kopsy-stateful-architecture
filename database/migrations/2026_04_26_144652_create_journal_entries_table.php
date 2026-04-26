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
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->uuid('fin_trans_id');
            $table->string('account_code');
            $table->uuid('user_id');
            $table->enum('position', ['Debit', 'Credit']);
            $table->decimal('nominal', 15, 2);
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('fin_trans_id')->references('id')->on('financial_transactions')->onDelete('cascade');
            $table->foreign('account_code')->references('account_code')->on('accounts')->onDelete('restrict');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['fin_trans_id', 'account_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
