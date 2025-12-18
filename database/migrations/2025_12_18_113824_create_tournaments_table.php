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
        Schema::create('tournaments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('organizer_id')->constrained('users')->onDelete('cascade');
            $table->enum('game', ['efootball', 'fc_mobile', 'dream_league_soccer']);
            $table->integer('max_participants');
            $table->decimal('entry_fee', 10, 2);
            $table->datetime('start_date');
            $table->enum('status', ['draft', 'open', 'in_progress', 'completed', 'cancelled'])->default('draft');
            $table->json('prize_distribution')->nullable();
            $table->integer('total_rounds')->nullable();
            $table->integer('current_round')->default(0);
            $table->timestamps();

            // Indexes
            $table->index('organizer_id');
            $table->index('game');
            $table->index('status');
            $table->index('start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournaments');
    }
};
