<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Override;

final class AppServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);

        RateLimiter::for('login', static function (Request $request): Limit {
            $emailInput = $request->input('email');
            $email = is_string($emailInput) ? mb_strtolower($emailInput) : '';

            return Limit::perMinute(5)->by($email . '|' . $request->ip());
        });

        RateLimiter::for('two-factor', static function (Request $request): Limit {
            $sessionId = $request->session()->get('login.id');
            $key = is_string($sessionId) ? $sessionId : '';

            return Limit::perMinute(5)->by($key);
        });
    }
}
