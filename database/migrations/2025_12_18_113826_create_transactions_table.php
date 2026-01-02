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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->nullable();
            $table->foreignId('wallet_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['credit', 'debit']);
            $table->decimal('amount', 10, 2);
            $table->decimal('balance_before', 10, 2);
            $table->decimal('balance_after', 10, 2);
            $table->enum('reason', ['initial_bonus', 'tournament_registration', 'tournament_prize', 'refund', 'admin_adjustment', 'tournament_entry_received', 'tournament_entry_refunded', 'tournament_profit', 'tournament_creation_fee']);
            $table->string('description')->nullable();
            $table->foreignId('tournament_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->index('wallet_id');
            $table->index('user_id');
            $table->index('type');
            $table->index('reason');
            $table->index('tournament_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
