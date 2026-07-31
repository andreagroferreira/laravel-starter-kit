<?php

declare(strict_types=1);

use App\Providers\HorizonServiceProvider;

arch()->preset()->php();
arch()->preset()->strict()->ignoring([
    // Horizon's service provider must override the framework's protected gate() method.
    HorizonServiceProvider::class,
]);
arch()->preset()->laravel();
arch()->preset()->security()->ignoring([
    'assert',
]);

arch('controllers')
    ->expect('App\Http\Controllers')
    ->not->toBeUsed();

//
