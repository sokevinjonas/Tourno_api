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
        Schema::table('organizer_profiles', function (Blueprint $table) {
            $table->text('rejection_reason')->nullable()->after('contrat_signer');
            $table->foreignId('processed_by_user_id')->nullable()->constrained('users')->onDelete('set null')->after('rejection_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizer_profiles', function (Blueprint $table) {
            $table->dropForeign(['processed_by_user_id']);
            $table->dropColumn(['rejection_reason', 'processed_by_user_id']);
        });
    }
};
