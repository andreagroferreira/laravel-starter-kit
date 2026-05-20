<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

it('renders the staff login page as an Inertia component', function (): void {
    get('/admin/login')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Auth/Login')
            ->where('canResetPassword', true)
            ->has('status'),
        );
});

it('renders the forgot password page as an Inertia component', function (): void {
    get('/admin/forgot-password')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page->component('Auth/ForgotPassword'));
});

it('renders the reset password page with token and email', function (): void {
    /** @var User $user */
    $user = User::factory()->create(['email' => 'staff@wc.io']);

    $token = Password::broker()->createToken($user);

    get('/admin/reset-password/' . $token . '?email=staff@wc.io')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Auth/ResetPassword')
            ->where('token', $token)
            ->where('email', 'staff@wc.io'),
        );
});

it('renders the verify email page for an unverified authenticated user', function (): void {
    /** @var User $user */
    $user = User::factory()->unverified()->create();

    actingAs($user)
        ->get('/admin/email/verify')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page->component('Auth/VerifyEmail'));
});

it('renders the confirm password page for an authenticated user', function (): void {
    /** @var User $user */
    $user = User::factory()->create();

    actingAs($user)
        ->get('/admin/user/confirm-password')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page->component('Auth/ConfirmPassword'));
});
