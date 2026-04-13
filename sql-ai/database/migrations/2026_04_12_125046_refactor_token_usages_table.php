<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('token_usages', function (Blueprint $table) {

            if (Schema::hasColumn('token_usages', 'tokens_used')) {
                $table->dropColumn('tokens_used');
            }

            $table->integer('input_tokens')->default(0);
            $table->integer('output_tokens')->default(0);
            $table->integer('total_tokens')->default(0);
            $table->decimal('cost', 10, 6)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('token_usages', function (Blueprint $table) {
            $table->dropColumn(['input_tokens', 'output_tokens', 'total_tokens', 'cost']);
            $table->integer('tokens_used')->default(0);
        });
    }
};