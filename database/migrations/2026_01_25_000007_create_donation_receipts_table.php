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
        Schema::create('donation_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donation_id')->constrained('donations')->cascadeOnDelete();
            $table->string('receipt_number')->unique();
            $table->timestamp('issued_at')->nullable();
            $table->string('pdf_path')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique('donation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donation_receipts');
    }
};
