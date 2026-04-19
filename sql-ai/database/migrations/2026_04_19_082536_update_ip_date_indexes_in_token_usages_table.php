<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('token_usages', function (Blueprint $table) {
            
            // Drop old indexes
            $table->dropUnique(['ip', 'date']);
            $table->dropIndex(['ip', 'date']);

            // Add new ones
            $table->unique(['ip', 'date', 'model', 'provider'], 'ip_date_model_provider_unique');
            $table->index(['ip', 'date', 'model', 'provider'], 'ip_date_model_provider_index');
        });
    }

    public function down(): void
    {
        Schema::table('token_usages', function (Blueprint $table) {

            // Drop new ones
            $table->dropUnique('ip_date_model_provider_unique');
            $table->dropIndex('ip_date_model_provider_index');

            // Restore old ones
            $table->unique(['ip', 'date']);
            $table->index(['ip', 'date']);
        });
    }
};