<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Builder;

arch('app uses strict types')
    // @phpstan-ignore-next-line method.notFound
    ->expect('App')
    ->toUseStrictTypes();

arch('packages use strict types')
    // @phpstan-ignore-next-line method.notFound
    ->expect('WizardingCode')
    ->toUseStrictTypes()
    ->ignoring('WizardingCode');

arch('no debug functions in source')
    // @phpstan-ignore-next-line method.notFound
    ->expect(['dd', 'dump', 'var_dump', 'ray', 'die', 'echo', 'print_r'])
    ->not->toBeUsed();

arch('controllers do not contain Eloquent queries')
    // @phpstan-ignore-next-line method.notFound
    ->expect('App\Http\Controllers')
    ->not->toUse([Builder::class, Illuminate\Database\Query\Builder::class]);
