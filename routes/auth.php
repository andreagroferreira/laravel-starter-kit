<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\ImpersonateController;
use App\Http\Controllers\Auth\LeaveImpersonationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth:web'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::post('/impersonate/{user}', ImpersonateController::class)->name('impersonate');
    Route::delete('/impersonate', LeaveImpersonationController::class)->name('impersonate.leave');
});
