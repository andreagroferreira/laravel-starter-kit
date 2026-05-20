<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Sleep;
use Illuminate\Support\Str;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| WizardingCode boilerplate — Pest configuration.
|
| RefreshDatabase by default. SQLite in-memory in phpunit.xml for speed.
| Use ->todo() to mark known-pending tests. Use ->skip() with explanation
| for environment-bound flakiness.
|--------------------------------------------------------------------------
*/

pest()
    ->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(function (): void {
        Str::createRandomStringsNormally();
        Str::createUuidsNormally();
        Http::preventStrayRequests();
        Process::preventStrayProcesses();
        Sleep::fake();

        $this->freezeTime();
    })
    ->in('Feature');

pest()
    ->extend(TestCase::class)
    ->in('Unit');

pest()
    ->extend(TestCase::class)
    ->in('Browser');

/*
|--------------------------------------------------------------------------
| Custom expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeOne', fn () => $this->toBe(1));

/**
 * @param array<int, string> $expected
 */
expect()->extend('toBeFillable', function (array $expected): void {
    /** @var Illuminate\Database\Eloquent\Model $model */
    $model = $this->value;
    expect($model->getFillable())->toBe($expected);
});

/**
 * @param array<int, string> $expected
 */
expect()->extend('toBeGuarded', function (array $expected): void {
    /** @var Illuminate\Database\Eloquent\Model $model */
    $model = $this->value;
    expect($model->getGuarded())->toBe($expected);
});

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function asStaff(): App\Models\User
{
    /** @var App\Models\User $user */
    $user = App\Models\User::factory()->create();
    Auth::guard('web')->login($user);

    return $user;
}

function something(): void
{
    // ..
}
