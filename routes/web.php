<?php

declare(strict_types=1);

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

Route::get('/', fn (): View => view('welcome'))->name('home');

Route::middleware(['web'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', fn (): Response => Inertia::render('Dashboard/Index'))->name('dashboard');
    Route::get('/inbox', fn (): Response => Inertia::render('Inbox/Index'))->name('inbox');
    Route::get('/customers', fn (): Response => Inertia::render('Customers/Index'))->name('customers');

    Route::prefix('settings')->name('settings.')->group(function (): void {
        Route::get('/', fn (): Response => Inertia::render('Settings/Index'))->name('index');
        Route::get('/members', fn (): Response => Inertia::render('Settings/Members'))->name('members');
        Route::get('/notifications', fn (): Response => Inertia::render('Settings/Notifications'))->name('notifications');
        Route::get('/security', fn (): Response => Inertia::render('Settings/Security'))->name('security');
    });
});
