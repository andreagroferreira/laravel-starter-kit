<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_overage_reports', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->date('period');
            $table->unsignedInteger('credits_reported')->default(0);
            $table->timestamps();

            $table->unique(['tenant_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_overage_reports');
    }
};
