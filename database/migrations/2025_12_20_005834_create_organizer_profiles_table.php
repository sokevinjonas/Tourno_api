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
            $table->uuid('uuid')->unique()->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('display_name')->nullable();
            $table->enum('badge', ['certified', 'verified', 'partner'])->nullable();
            $table->string('avatar_url')->nullable();
            $table->string('avatar_initial', 2)->nullable();

            $table->enum('nature_document', ['cnib', 'permis', 'passport'])->nullable();
            $table->string('doc_recto')->nullable();
            $table->string('doc_verso')->nullable();
            $table->string('contrat_signer')->nullable();
            $table->enum('status', ['attente', 'valider', 'rejeter'])->nullable();

            $table->text('bio')->nullable();
            $table->json('social_links')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            $table->text('rejection_reason')->nullable();
            $table->foreignId('processed_by_user_id')->nullable()->constrained('users')->onDelete('set null');

            // Indexes
            $table->unique('user_id');
            $table->index('is_featured');
            $table->index('badge');
            $table->index('status');
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
