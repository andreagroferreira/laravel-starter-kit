<?php

declare(strict_types=1);

use App\Http\Controllers\AiArticleController;
use App\Http\Controllers\AiCopilotController;
use App\Http\Controllers\AiCopyController;
use App\Http\Controllers\AiGenerationController;
use App\Http\Controllers\AiSeoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LeadExportController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\MediaPickerController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PublishPageController;
use App\Http\Controllers\PublishPostController;
use App\Http\Controllers\PublishSiteController;
use App\Http\Controllers\RedirectController;
use App\Http\Controllers\Settings\AuditLogController;
use App\Http\Controllers\Settings\BillingCheckoutController;
use App\Http\Controllers\Settings\BillingController;
use App\Http\Controllers\Settings\BillingPortalController;
use App\Http\Controllers\Settings\BrandProfileController;
use App\Http\Controllers\Settings\InviteMemberController;
use App\Http\Controllers\Settings\MembersController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\RemoveMemberController;
use App\Http\Controllers\Settings\SecurityController;
use App\Http\Controllers\Settings\TokenController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/', [DashboardController::class, 'index'])->name('home');

    Route::get('/media', [MediaController::class, 'index'])->name('media.index');
    Route::get('/media/picker', MediaPickerController::class)->name('media.picker');
    Route::post('/media', [MediaController::class, 'store'])->name('media.store');
    Route::put('/media/{media}', [MediaController::class, 'update'])->name('media.update');
    Route::delete('/media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');

    Route::get('/ai', AiCopilotController::class)->name('ai.index');
    Route::get('/ai/generations/{generation}', AiGenerationController::class)->name('ai.generations.show');

    Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
    Route::get('/leads/export', LeadExportController::class)->name('leads.export');
    Route::put('/leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
    Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');

    Route::get('/sites', [SiteController::class, 'index'])->name('sites.index');
    Route::post('/sites', [SiteController::class, 'store'])->name('sites.store');

    Route::middleware('site.tenant')->group(function (): void {
        Route::get('/sites/{site}', [SiteController::class, 'show'])->name('sites.show');
        Route::delete('/sites/{site}', [SiteController::class, 'destroy'])->name('sites.destroy');
        Route::post('/sites/{site}/publish', PublishSiteController::class)->name('sites.publish');
        Route::post('/sites/{site}/pages', [PageController::class, 'store'])->name('pages.store');
        Route::get('/sites/{site}/pages/{page}', [PageController::class, 'show'])->name('pages.show');
        Route::put('/sites/{site}/pages/{page}', [PageController::class, 'update'])->name('pages.update');
        Route::delete('/sites/{site}/pages/{page}', [PageController::class, 'destroy'])->name('pages.destroy');
        Route::post('/sites/{site}/pages/{page}/publish', PublishPageController::class)->name('pages.publish');

        Route::get('/sites/{site}/posts', [PostController::class, 'index'])->name('posts.index');
        Route::post('/sites/{site}/posts', [PostController::class, 'store'])->name('posts.store');
        Route::get('/sites/{site}/posts/{post}', [PostController::class, 'show'])->name('posts.show');
        Route::put('/sites/{site}/posts/{post}', [PostController::class, 'update'])->name('posts.update');
        Route::delete('/sites/{site}/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
        Route::post('/sites/{site}/posts/{post}/publish', PublishPostController::class)->name('posts.publish');

        Route::get('/sites/{site}/menus', [MenuController::class, 'index'])->name('menus.index');
        Route::post('/sites/{site}/menus', [MenuController::class, 'store'])->name('menus.store');
        Route::put('/sites/{site}/menus/{menu}', [MenuController::class, 'update'])->name('menus.update');
        Route::delete('/sites/{site}/menus/{menu}', [MenuController::class, 'destroy'])->name('menus.destroy');

        Route::get('/sites/{site}/forms', [FormController::class, 'index'])->name('forms.index');
        Route::post('/sites/{site}/forms', [FormController::class, 'store'])->name('forms.store');
        Route::delete('/sites/{site}/forms/{form}', [FormController::class, 'destroy'])->name('forms.destroy');

        Route::get('/sites/{site}/redirects', [RedirectController::class, 'index'])->name('redirects.index');
        Route::post('/sites/{site}/redirects', [RedirectController::class, 'store'])->name('redirects.store');
        Route::delete('/sites/{site}/redirects/{redirect}', [RedirectController::class, 'destroy'])->name('redirects.destroy');

        Route::post('/sites/{site}/ai/copy', AiCopyController::class)->middleware('ai.credits')->name('ai.copy');
        Route::post('/sites/{site}/ai/article', AiArticleController::class)->middleware('ai.credits')->name('ai.article');
        Route::post('/sites/{site}/ai/seo', AiSeoController::class)->middleware('ai.credits')->name('ai.seo');
    });

    Route::prefix('settings')->name('settings.')->group(function (): void {
        Route::get('/', ProfileController::class)->name('profile');
        Route::get('/members', MembersController::class)->name('members');
        Route::post('/members', InviteMemberController::class)->name('members.invite');
        Route::delete('/members/{user}', RemoveMemberController::class)->name('members.remove');
        Route::get('/brand', [BrandProfileController::class, 'show'])->name('brand');
        Route::put('/brand', [BrandProfileController::class, 'update'])->name('brand.update');
        Route::get('/api', [TokenController::class, 'show'])->name('api');
        Route::post('/api/tokens', [TokenController::class, 'store'])->name('api.tokens.store');
        Route::delete('/api/tokens/{tokenId}', [TokenController::class, 'destroy'])->name('api.tokens.destroy');
        Route::get('/audit', AuditLogController::class)->name('audit');
        Route::get('/security', SecurityController::class)->name('security');
        Route::get('/billing', [BillingController::class, 'show'])->name('billing');
        Route::get('/billing/checkout/{plan}', BillingCheckoutController::class)->name('billing.checkout');
        Route::get('/billing/portal', BillingPortalController::class)->name('billing.portal');
    });
});
