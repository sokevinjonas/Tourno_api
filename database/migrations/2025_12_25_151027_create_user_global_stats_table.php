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
        Schema::create('user_global_stats', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');

            // Points de classement global (tous jeux confondus)
            $table->integer('global_rating')->default(1000);

            // Statistiques tournois (tous jeux)
            $table->integer('total_tournaments_played')->default(0);
            $table->integer('total_tournaments_won')->default(0);

            // Statistiques matchs (tous jeux)
            $table->integer('total_matches_played')->default(0);
            $table->integer('total_matches_won')->default(0);
            $table->integer('total_matches_lost')->default(0);
            $table->integer('total_matches_draw')->default(0);

            // Récompenses (tous jeux)
            $table->decimal('total_prize_money', 10, 2)->default(0);

            // Méta
            $table->timestamp('last_tournament_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_global_stats');
    }
};
