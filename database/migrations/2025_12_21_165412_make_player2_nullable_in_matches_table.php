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
        Schema::table('matches', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['player2_id']);

            // Modify the column to be nullable
            $table->foreignId('player2_id')->nullable()->change();

            // Re-add the foreign key constraint
            $table->foreign('player2_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['player2_id']);

            // Make the column NOT nullable
            $table->foreignId('player2_id')->nullable(false)->change();

            // Re-add the foreign key constraint
            $table->foreign('player2_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }
};
