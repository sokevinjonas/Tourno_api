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
        Schema::table('tournament_matches', function (Blueprint $table) {
            // Renommer l'ancien champ pour clarté (1h devient 30min)
            $table->renameColumn('deadline_warning_sent_at', 'deadline_warning_30min_sent_at');

            // Ajouter le nouveau champ pour l'avertissement 15min
            $table->timestamp('deadline_warning_15min_sent_at')->nullable()->after('deadline_warning_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tournament_matches', function (Blueprint $table) {
            // Supprimer le champ 15min
            $table->dropColumn('deadline_warning_15min_sent_at');

            // Renommer le champ 30min pour revenir à l'ancien nom
            $table->renameColumn('deadline_warning_30min_sent_at', 'deadline_warning_sent_at');
        });
    }
};
