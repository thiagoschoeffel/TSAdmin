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
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['created_by_id']);
            $table->dropForeign(['updated_by_id']);

            $table->foreign('created_by_id')
                ->references('id')->on('users')
                ->restrictOnDelete();

            $table->foreign('updated_by_id')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['created_by_id']);
            $table->dropForeign(['updated_by_id']);

            $table->foreign('created_by_id')
                ->references('id')->on('users')
                ->nullOnDelete();

            $table->foreign('updated_by_id')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }
};
