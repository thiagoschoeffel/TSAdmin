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
        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('created_by_id')->nullable()->after('owner_id')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_id')->nullable()->after('created_by_id')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['created_by_id']);
            $table->dropForeign(['updated_by_id']);
            $table->dropColumn(['created_by_id', 'updated_by_id']);
        });
    }
};
