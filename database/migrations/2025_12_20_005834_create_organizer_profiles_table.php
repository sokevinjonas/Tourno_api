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
        Schema::create('organizer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('display_name')->nullable();
            $table->enum('badge', ['certified', 'verified', 'partner'])->nullable();
            $table->string('avatar_url')->nullable();
            $table->string('avatar_initial', 2)->nullable();
            $table->text('bio')->nullable();
            $table->json('social_links')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            // Indexes
            $table->unique('user_id');
            $table->index('is_featured');
            $table->index('badge');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizer_profiles');
    }
};
