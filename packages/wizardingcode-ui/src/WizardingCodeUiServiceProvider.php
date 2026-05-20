<?php

declare(strict_types=1);

namespace WizardingCode\Ui;

use Illuminate\Support\ServiceProvider;
use Override;

final class WizardingCodeUiServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Vue components live in resources/components/, exposed via the
        // tsconfig path alias @wizardingcode/ui. No PHP-side publishing
        // is required for the JS bundle; Vite resolves the alias.
        //
        // If/when we need Blade-side renderers, register them here.
    }

    #[Override]
    public function register(): void
    {
        // No bindings yet.
    }
}
