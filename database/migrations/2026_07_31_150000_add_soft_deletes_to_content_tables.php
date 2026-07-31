<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table): void {
            $table->softDeletes();
            $table->dropUnique(['tenant_id', 'slug']);
        });

        Schema::table('pages', function (Blueprint $table): void {
            $table->softDeletes();
            $table->dropUnique(['site_id', 'slug']);
        });

        Schema::table('posts', function (Blueprint $table): void {
            $table->softDeletes();
            $table->dropUnique(['site_id', 'slug']);
        });

        // Partial unique indexes: slugs stay unique among live rows, while
        // soft-deleted rows keep their slug without blocking reuse.
        DB::statement('CREATE UNIQUE INDEX sites_tenant_id_slug_unique ON sites (tenant_id, slug) WHERE deleted_at IS NULL');
        DB::statement('CREATE UNIQUE INDEX pages_site_id_slug_unique ON pages (site_id, slug) WHERE deleted_at IS NULL');
        DB::statement('CREATE UNIQUE INDEX posts_site_id_slug_unique ON posts (site_id, slug) WHERE deleted_at IS NULL');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS sites_tenant_id_slug_unique');
        DB::statement('DROP INDEX IF EXISTS pages_site_id_slug_unique');
        DB::statement('DROP INDEX IF EXISTS posts_site_id_slug_unique');

        Schema::table('sites', function (Blueprint $table): void {
            $table->dropSoftDeletes();
            $table->unique(['tenant_id', 'slug']);
        });

        Schema::table('pages', function (Blueprint $table): void {
            $table->dropSoftDeletes();
            $table->unique(['site_id', 'slug']);
        });

        Schema::table('posts', function (Blueprint $table): void {
            $table->dropSoftDeletes();
            $table->unique(['site_id', 'slug']);
        });
    }
};
