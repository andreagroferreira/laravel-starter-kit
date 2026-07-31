<?php

declare(strict_types=1);

use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

it('updates the user password with validation', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    (new UpdateUserPassword)->update($user, [
        'current_password' => 'password',
        'password' => 'new-secret-password',
        'password_confirmation' => 'new-secret-password',
    ]);

    expect(Hash::check('new-secret-password', $user->refresh()->password))->toBeTrue();
});

it('rejects a wrong current password', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    (new UpdateUserPassword)->update($user, [
        'current_password' => 'wrong',
        'password' => 'new-secret-password',
        'password_confirmation' => 'new-secret-password',
    ]);
})->throws(ValidationException::class);

it('resets the user password', function (): void {
    $user = User::factory()->create();

    (new ResetUserPassword)->reset($user, [
        'password' => 'brand-new-password',
        'password_confirmation' => 'brand-new-password',
    ]);

    expect(Hash::check('brand-new-password', $user->refresh()->password))->toBeTrue();
});

it('updates profile information without email change', function (): void {
    $user = User::factory()->create(['name' => 'Old', 'email' => 'old@example.com']);

    (new UpdateUserProfileInformation)->update($user, [
        'name' => 'New Name',
        'email' => 'old@example.com',
    ]);

    expect($user->refresh()->name)->toBe('New Name')
        ->and($user->email_verified_at)->not->toBeNull();
});

it('resets verification when the email changes', function (): void {
    $user = User::factory()->create(['email' => 'old@example.com']);

    (new UpdateUserProfileInformation)->update($user, [
        'name' => $user->name,
        'email' => 'new@example.com',
    ]);

    expect($user->refresh()->email)->toBe('new@example.com')
        ->and($user->email_verified_at)->toBeNull();
});

it('validates profile input', function (): void {
    $user = User::factory()->create();

    (new UpdateUserProfileInformation)->update($user, [
        'name' => '',
        'email' => 'not-an-email',
    ]);
})->throws(ValidationException::class);
