<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('schemas', function (Blueprint $table) {
            $table->id();

            // Link to user
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id')->nullable()->index();

            // Schema name (user-defined)
            $table->string('name')->nullable();

            // Original uploaded file (optional but useful)
            $table->string('file_path')->nullable();

            // Parsed schema (JSON)
            $table->longText('schema_json');

            // Optional: raw SQL (for debugging / reprocessing)
            $table->longText('raw_sql')->nullable();

            // Optional metadata
            $table->integer('tables_count')->default(0);
            $table->integer('columns_count')->default(0);

            // Active schema flag (if user uploads multiple)
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schemas');
    }
};