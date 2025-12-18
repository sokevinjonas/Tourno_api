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
        Schema::create('rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->onDelete('cascade');
            $table->integer('round_number');
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->datetime('start_date')->nullable();
            $table->datetime('end_date')->nullable();
            $table->timestamps();

            // Indexes
            $table->unique(['tournament_id', 'round_number']);
            $table->index('tournament_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rounds');
    }
};
