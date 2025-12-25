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
        Schema::create('user_game_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('game', ['efootball', 'fc_mobile', 'dream_league_soccer']);

            // Points de classement (ELO-like)
            $table->integer('rating_points')->default(1000);

            // Statistiques tournois (pour calculs internes)
            $table->integer('tournaments_played')->default(0);
            $table->integer('tournaments_won')->default(0);

            // Statistiques matchs
            $table->integer('total_matches_played')->default(0);
            $table->integer('total_matches_won')->default(0);
            $table->integer('total_matches_lost')->default(0);
            $table->integer('total_matches_draw')->default(0);

            // Récompenses
            $table->decimal('total_prize_money', 10, 2)->default(0);

            // Méta
            $table->timestamp('last_tournament_at')->nullable();
            $table->timestamps();

            // Index unique: un seul enregistrement par utilisateur et par jeu
            $table->unique(['user_id', 'game']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_game_stats');
    }
};
