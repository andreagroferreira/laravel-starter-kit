<?php

declare(strict_types=1);

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\Settings\MembersController;
use App\Http\Controllers\Settings\NotificationsController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SecurityController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('home');
Route::get('/inbox', [InboxController::class, 'index'])->name('inbox');
Route::get('/customers', [CustomerController::class, 'index'])->name('customers');

Route::prefix('settings')->name('settings.')->group(function (): void {
    Route::get('/', ProfileController::class)->name('profile');
    Route::get('/members', MembersController::class)->name('members');
    Route::get('/notifications', NotificationsController::class)->name('notifications');
    Route::get('/security', SecurityController::class)->name('security');
});
