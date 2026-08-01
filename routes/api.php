<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\PublicApi\FormSubmissionController;
use App\Http\Controllers\Api\V1\PublicApi\ResolveSiteController;
use App\Http\Controllers\Api\V1\SiteFeedController;
use App\Http\Controllers\Api\V1\SitePageController;
use App\Http\Controllers\Api\V1\SiteSchemaController;
use App\Http\Controllers\Api\V1\SitesController;
use App\Http\Controllers\Api\V1\SiteSitemapController;
use Illuminate\Support\Facades\Route;

// Public endpoints for the renderer and crawlers. Sites are addressed by
// id (resolved by hostname first) — never by slug, which is only unique
// per tenant.
Route::prefix('v1/public')->name('api.v1.public.')->group(function (): void {
    Route::get('/resolve', ResolveSiteController::class)
        ->middleware('throttle:60,1')
        ->name('resolve');

    Route::prefix('sites/{site}')->group(function (): void {
        Route::get('/feed.rss', SiteFeedController::class)->name('feed');
        Route::get('/sitemap.xml', SiteSitemapController::class)->name('sitemap');
        Route::post('/forms/{form}/submissions', FormSubmissionController::class)
            ->middleware('throttle:form-submissions')
            ->name('forms.submissions.store');
    });
});

Route::prefix('v1')->name('api.v1.')->middleware(['auth:sanctum', 'abilities:read', 'site.tenant'])->group(function (): void {
    Route::get('/sites', SitesController::class)->name('sites.index');
    Route::get('/sites/{site:slug}/schema', SiteSchemaController::class)->name('sites.schema');
    Route::get('/sites/{site:slug}/pages/{slug}', SitePageController::class)
        ->where('slug', '.*')
        ->name('sites.pages.show');
});
