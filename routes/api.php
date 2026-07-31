<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\SiteFeedController;
use App\Http\Controllers\Api\V1\SitePageController;
use App\Http\Controllers\Api\V1\SiteSchemaController;
use App\Http\Controllers\Api\V1\SitesController;
use App\Http\Controllers\Api\V1\SiteSitemapController;
use Illuminate\Support\Facades\Route;

// Public per-site endpoints (RSS readers, crawlers) — published content only.
Route::prefix('v1/sites/{site:slug}')->name('api.v1.sites.')->group(function (): void {
    Route::get('/feed.rss', SiteFeedController::class)->name('feed');
    Route::get('/sitemap.xml', SiteSitemapController::class)->name('sitemap');
});

Route::prefix('v1')->name('api.v1.')->middleware(['auth:sanctum', 'abilities:read', 'site.tenant'])->group(function (): void {
    Route::get('/sites', SitesController::class)->name('sites.index');
    Route::get('/sites/{site:slug}/schema', SiteSchemaController::class)->name('sites.schema');
    Route::get('/sites/{site:slug}/pages/{slug}', SitePageController::class)
        ->where('slug', '.*')
        ->name('sites.pages.show');
});
