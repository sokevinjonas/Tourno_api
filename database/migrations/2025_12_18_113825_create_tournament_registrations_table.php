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
        Schema::create('tournament_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('game_account_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['registered', 'withdrawn', 'disqualified'])->default('registered');
            $table->integer('tournament_points')->default(0);
            $table->integer('wins')->default(0);
            $table->integer('draws')->default(0);
            $table->integer('losses')->default(0);
            $table->integer('final_rank')->nullable();
            $table->decimal('prize_won', 10, 2)->nullable();
            $table->timestamps();

            // Indexes
            $table->unique(['tournament_id', 'user_id']);
            $table->index('tournament_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournament_registrations');
    }
};
