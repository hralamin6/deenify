<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_attempts', function (Blueprint $table) {
            // Add manual payment gateways to the enum
            $table->enum('gateway', ['bkash', 'nagad', 'rocket', 'sslcommerz', 'shurjopay', 'aamarpay'])->change();
        });
    }

    public function down(): void
    {
        Schema::table('payment_attempts', function (Blueprint $table) {
            $table->enum('gateway', ['bkash', 'nagad', 'sslcommerz', 'shurjopay', 'aamarpay'])->change();
        });
    }
};
