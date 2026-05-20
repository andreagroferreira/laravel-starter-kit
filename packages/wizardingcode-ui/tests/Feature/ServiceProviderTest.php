<?php

declare(strict_types=1);

use WizardingCode\Ui\WizardingCodeUiServiceProvider;

it('WizardingCodeUiServiceProvider class exists', function (): void {
    expect(class_exists(WizardingCodeUiServiceProvider::class))->toBeTrue();
});

it('boots the WizardingCodeUiServiceProvider', function (): void {
    /** @var Orchestra\Testbench\TestCase $this */
    $this->app->register(WizardingCodeUiServiceProvider::class);

    expect($this->app->getProviders(WizardingCodeUiServiceProvider::class))
        ->not->toBeEmpty();
});
