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
        Schema::create('organizer_followers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Le follower (joueur)
            $table->foreignId('organizer_id')->constrained('users')->onDelete('cascade'); // L'organisateur suivi
            $table->timestamps();

            // Indexes
            $table->unique(['user_id', 'organizer_id']); // Un utilisateur ne peut suivre un organisateur qu'une fois
            $table->index('user_id');
            $table->index('organizer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizer_followers');
    }
};
