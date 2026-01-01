<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE transactions MODIFY COLUMN reason ENUM('initial_bonus', 'tournament_registration', 'tournament_prize', 'refund', 'admin_adjustment', 'tournament_entry_received', 'tournament_entry_refunded', 'tournament_profit') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE transactions MODIFY COLUMN reason ENUM('initial_bonus', 'tournament_registration', 'tournament_prize', 'refund', 'admin_adjustment', 'tournament_entry_received', 'tournament_entry_refunded') NOT NULL");
    }
};
