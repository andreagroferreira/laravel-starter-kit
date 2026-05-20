<?php

declare(strict_types=1);

use App\Providers\HorizonServiceProvider;

arch()->preset()->php();
arch()->preset()->strict()->ignoring([
    // Horizon's ApplicationServiceProvider mandates a protected gate() method.
    // The class itself is already declared final; the framework hook stays protected.
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
