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
        Schema::create('tournament_wallet_locks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->onDelete('cascade');
            $table->foreignId('wallet_id')->constrained()->onDelete('cascade');
            $table->decimal('locked_amount', 10, 2);
            $table->enum('status', ['locked', 'processing_payouts', 'released'])->default('locked');
            $table->decimal('paid_out', 10, 2)->default(0);
            $table->timestamps();

            // Indexes
            $table->index(['tournament_id', 'wallet_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournament_wallet_locks');
    }
};
