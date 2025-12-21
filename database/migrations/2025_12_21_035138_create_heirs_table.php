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
        Schema::create('heirs', function (Blueprint $table) {
            $table->string('nik')->primary();
            $table->string('name');
            $table->enum('relationship', ['Anak', 'Suami', 'Istri', 'Sepupu', 'Saudara']);
            $table->string('contact');
            $table->foreignUuid('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('heirs');
    }
};
