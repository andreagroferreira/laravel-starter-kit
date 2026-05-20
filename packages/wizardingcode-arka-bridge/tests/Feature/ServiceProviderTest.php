<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use WizardingCode\ArkaBridge\ArkaBridge;
use WizardingCode\ArkaBridge\ArkaBridgeServiceProvider;

uses()->beforeEach(function (): void {
    /** @var Orchestra\Testbench\TestCase $this */
    $this->loadServiceProvidersFromArray = [];
});

it('boots the ArkaBridgeServiceProvider', function (): void {
    $providers = config('app.providers', []);

    expect(class_exists(ArkaBridgeServiceProvider::class))->toBeTrue();
});

it('exposes the arka.bridge singleton', function (): void {
    /** @var Application $app */
    $app = $this->app;
    $app->register(ArkaBridgeServiceProvider::class);

    expect($app->make('arka.bridge'))->toBeInstanceOf(ArkaBridge::class);
});

it('registers the arka:sync command', function (): void {
    /** @var Application $app */
    $app = $this->app;
    $app->register(ArkaBridgeServiceProvider::class);

    $commands = $this->artisan('list');
    $commands->expectsOutputToContain('arka:sync');
});
