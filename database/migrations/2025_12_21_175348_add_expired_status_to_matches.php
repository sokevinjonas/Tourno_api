<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For SQLite, we need to recreate the table with the new enum value
        DB::statement("
            CREATE TABLE matches_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                tournament_id INTEGER NOT NULL,
                round_id INTEGER NOT NULL,
                player1_id INTEGER NOT NULL,
                player2_id INTEGER,
                player1_score INTEGER,
                player2_score INTEGER,
                winner_id INTEGER,
                status TEXT CHECK(status IN ('scheduled', 'in_progress', 'pending_validation', 'completed', 'disputed', 'expired')) DEFAULT 'scheduled',
                scheduled_at TEXT,
                deadline_at TEXT,
                completed_at TEXT,
                created_at TEXT,
                updated_at TEXT,
                FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE,
                FOREIGN KEY (round_id) REFERENCES rounds(id) ON DELETE CASCADE,
                FOREIGN KEY (player1_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (player2_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (winner_id) REFERENCES users(id) ON DELETE SET NULL
            )
        ");

        DB::statement("INSERT INTO matches_new SELECT * FROM matches");
        DB::statement("DROP TABLE matches");
        DB::statement("ALTER TABLE matches_new RENAME TO matches");

        // Recreate indexes
        DB::statement("CREATE INDEX matches_tournament_id_index ON matches(tournament_id)");
        DB::statement("CREATE INDEX matches_round_id_index ON matches(round_id)");
        DB::statement("CREATE INDEX matches_player1_id_index ON matches(player1_id)");
        DB::statement("CREATE INDEX matches_player2_id_index ON matches(player2_id)");
        DB::statement("CREATE INDEX matches_winner_id_index ON matches(winner_id)");
        DB::statement("CREATE INDEX matches_status_index ON matches(status)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse: remove 'expired' from enum
        DB::statement("
            CREATE TABLE matches_old (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                tournament_id INTEGER NOT NULL,
                round_id INTEGER NOT NULL,
                player1_id INTEGER NOT NULL,
                player2_id INTEGER,
                player1_score INTEGER,
                player2_score INTEGER,
                winner_id INTEGER,
                status TEXT CHECK(status IN ('scheduled', 'in_progress', 'pending_validation', 'completed', 'disputed')) DEFAULT 'scheduled',
                scheduled_at TEXT,
                deadline_at TEXT,
                completed_at TEXT,
                created_at TEXT,
                updated_at TEXT,
                FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE,
                FOREIGN KEY (round_id) REFERENCES rounds(id) ON DELETE CASCADE,
                FOREIGN KEY (player1_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (player2_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (winner_id) REFERENCES users(id) ON DELETE SET NULL
            )
        ");

        DB::statement("INSERT INTO matches_old SELECT * FROM matches WHERE status != 'expired'");
        DB::statement("DROP TABLE matches");
        DB::statement("ALTER TABLE matches_old RENAME TO matches");

        // Recreate indexes
        DB::statement("CREATE INDEX matches_tournament_id_index ON matches(tournament_id)");
        DB::statement("CREATE INDEX matches_round_id_index ON matches(round_id)");
        DB::statement("CREATE INDEX matches_player1_id_index ON matches(player1_id)");
        DB::statement("CREATE INDEX matches_player2_id_index ON matches(player2_id)");
        DB::statement("CREATE INDEX matches_winner_id_index ON matches(winner_id)");
        DB::statement("CREATE INDEX matches_status_index ON matches(status)");
    }
};
