<?php

declare(strict_types=1);

use App\Jobs\Ai\AiGenerationJob;
use App\Providers\HorizonServiceProvider;

arch()->preset()->php();
arch()->preset()->strict()->ignoring([
    // Horizon's service provider must override the framework's protected gate() method.
    HorizonServiceProvider::class,
    // Template-method base class for the AI generation jobs.
    AiGenerationJob::class,
]);
arch()->preset()->laravel();
arch()->preset()->security()->ignoring([
    'assert',
]);

arch('controllers')
    ->expect('App\Http\Controllers')
    ->not->toBeUsed();

//
