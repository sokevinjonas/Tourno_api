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
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->onDelete('cascade');
            $table->foreignId('round_id')->constrained()->onDelete('cascade');
            $table->foreignId('player1_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('player2_id')->constrained('users')->onDelete('cascade');
            $table->integer('player1_score')->nullable();
            $table->integer('player2_score')->nullable();
            $table->foreignId('winner_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['scheduled', 'in_progress', 'pending_validation', 'completed', 'disputed'])->default('scheduled');
            $table->datetime('scheduled_at')->nullable();
            $table->datetime('completed_at')->nullable();

            $table->datetime('deadline_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('tournament_id');
            $table->index('round_id');
            $table->index('player1_id');
            $table->index('player2_id');
            $table->index('winner_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
