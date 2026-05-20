<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Auth\EmailVerificationController;
use App\Http\Controllers\Api\V1\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\Auth\ResetPasswordController;
use App\Http\Controllers\Api\V1\Auth\SocialCallbackController;
use App\Http\Controllers\Api\V1\Auth\SocialRedirectController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::post('register', RegisterController::class)
        ->middleware('throttle:5,1')
        ->name('api.v1.auth.register');

    Route::post('login', LoginController::class)
        ->middleware('throttle:5,1')
        ->name('api.v1.auth.login');

    Route::post('password/forgot', ForgotPasswordController::class)
        ->middleware('throttle:3,1')
        ->name('api.v1.auth.password.forgot');

    Route::post('password/reset', ResetPasswordController::class)
        ->middleware('throttle:5,1')
        ->name('api.v1.auth.password.reset');

    Route::get('verify-email/{id}/{hash}', EmailVerificationController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('api.v1.auth.verify-email');

    Route::get('social/{provider}', SocialRedirectController::class)
        ->middleware('throttle:10,1')
        ->name('api.v1.auth.social.redirect');

    Route::post('social/{provider}/callback', SocialCallbackController::class)
        ->middleware('throttle:10,1')
        ->name('api.v1.auth.social.callback');
});

Route::middleware('auth:customer')->prefix('auth')->group(function (): void {
    Route::post('logout', LogoutController::class)->name('api.v1.auth.logout');
});
