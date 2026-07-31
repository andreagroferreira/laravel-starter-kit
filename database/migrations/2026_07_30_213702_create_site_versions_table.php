<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_versions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('site_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('schema');
            $table->string('renderer_version')->nullable();
            $table->string('origin')->default('human');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['site_id', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_versions');
    }
};
