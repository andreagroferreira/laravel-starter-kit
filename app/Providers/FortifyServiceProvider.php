<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Fortify;

final class FortifyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Fortify::loginView(static fn (): Response => Inertia::render('Auth/Login', [
            'canResetPassword' => true,
            'status' => session('status'),
        ]));

        Fortify::twoFactorChallengeView(static fn (): Response => Inertia::render('Auth/TwoFactorChallenge'));

        Fortify::requestPasswordResetLinkView(static fn (): Response => Inertia::render('Auth/ForgotPassword', [
            'status' => session('status'),
        ]));

        Fortify::resetPasswordView(static fn (Request $request): Response => Inertia::render('Auth/ResetPassword', [
            'token' => (string) $request->route('token'),
            'email' => (string) $request->query('email', ''),
        ]));

        Fortify::verifyEmailView(static fn (): Response => Inertia::render('Auth/VerifyEmail', [
            'status' => session('status'),
        ]));

        Fortify::confirmPasswordView(static fn (): Response => Inertia::render('Auth/ConfirmPassword'));
    }
}
