<?php

declare(strict_types=1);

namespace WizardingCode\ArkaBridge;

use Illuminate\Support\ServiceProvider;
use Override;
use WizardingCode\ArkaBridge\Console\SyncCommand;

final class ArkaBridgeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncCommand::class,
            ]);
        }
    }

    #[Override]
    public function register(): void
    {
        $this->app->singleton('arka.bridge', fn (): ArkaBridge => new ArkaBridge);
    }
}
