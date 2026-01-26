<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manual_payment_proofs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_attempt_id')->constrained()->cascadeOnDelete();
            $table->string('transaction_id'); // User-provided transaction ID
            $table->string('sender_number'); // Phone number used to send money
            $table->string('screenshot_path')->nullable(); // Path to uploaded screenshot
            $table->text('notes')->nullable(); // Additional notes from user
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->text('admin_notes')->nullable(); // Notes from admin during verification
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_payment_proofs');
    }
};
