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
            $table->enum('format', ['single_elimination', 'swiss', 'champions_league']);
            $table->integer('max_participants');
            $table->decimal('entry_fee', 10, 2);
            $table->datetime('start_date');
            $table->enum('status', ['draft', 'open', 'in_progress', 'completed', 'cancelled'])->default('draft');
            $table->json('prize_distribution')->nullable();
            $table->json('rules')->nullable();
            $table->integer('total_rounds')->nullable();
            $table->integer('current_round')->default(0);

            $table->enum('visibility', ['public', 'private'])->default('public');
            $table->string('unique_url', 50)->nullable()->unique();
            $table->decimal('creation_fee_paid', 10, 2)->default(0);
            $table->datetime('full_since')->nullable();
            $table->boolean('auto_managed')->default(false);
            $table->datetime('actual_start_date')->nullable();

            $table->integer('tournament_duration_days')->nullable();
            $table->enum('time_slot', ['morning', 'afternoon', 'evening'])->default('evening');
            $table->integer('match_deadline_minutes')->default(60);
            $table->timestamps();

            // Indexes
            $table->index('organizer_id');
            $table->index('game');
            $table->index('format');
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
