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
        Schema::create('coin_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['deposit', 'withdrawal']);

            // Montants
            $table->decimal('amount_coins', 15, 2);
            $table->decimal('amount_money', 15, 2);
            $table->decimal('fee_percentage', 5, 2)->default(0);
            $table->decimal('fee_amount', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2);
            $table->string('currency', 10)->default('XOF');

            // Informations paiement
            $table->string('payment_method')->nullable();
            $table->string('payment_provider')->default('fusionpay');
            $table->string('payment_phone')->nullable();

            // FusionPay spécifique
            $table->string('fusionpay_token')->unique()->nullable();
            $table->string('fusionpay_transaction_number')->nullable();
            $table->string('fusionpay_event')->nullable();

            // Gestion manuelle (retraits)
            $table->string('proof_screenshot')->nullable();
            $table->text('admin_note')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('processed_at')->nullable();
            $table->text('rejection_reason')->nullable();

            // Statuts et dates
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled', 'rejected'])->default('pending');
            $table->timestamps();

            // Index pour performance
            $table->index('user_id');
            $table->index('status');
            $table->index('type');
            $table->index('fusionpay_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coin_transactions');
    }
};
