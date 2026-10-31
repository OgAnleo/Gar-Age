<?php

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;

it('verifies new users at once and sends no mail when verification is off', function () {
    config(['auth.verify_email' => false]);
    Notification::fake();

    $this->post(route('register.store'), [
        'name' => 'Ana',
        'business_name' => 'Trasteros Norte',
        'email' => 'ana@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'ana@example.com')->sole();

    expect($user->hasVerifiedEmail())->toBeTrue();
    Notification::assertNothingSent();
    $this->get('/trasteros-norte/dashboard')->assertOk();
});

it('keeps a changed email verified when verification is off', function () {
    config(['auth.verify_email' => false]);
    $user = User::factory()->create();

    $this->actingAs($user)->patch(route('profile.update'), ['name' => $user->name, 'email' => 'new@example.com']);

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

it('asks new users to verify their email by default', function () {
    Notification::fake();

    $this->post(route('register.store'), [
        'name' => 'Ana',
        'business_name' => 'Trasteros Norte',
        'email' => 'ana@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'ana@example.com')->sole();

    expect($user->hasVerifiedEmail())->toBeFalse();
    Notification::assertSentTo($user, VerifyEmail::class);
});
