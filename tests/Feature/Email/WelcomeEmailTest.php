<?php

use App\Mail\WelcomeEmail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

it('queues welcome email after registration', function () {
    Mail::fake();

    $this->post(route('register.store'), [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    Mail::assertQueued(WelcomeEmail::class, function (WelcomeEmail $mail) {
        return $mail->user->email === 'john@example.com';
    });
});

it('addresses the user by first name', function () {
    $user = User::factory()->create(['first_name' => 'Jane']);

    $mailable = new WelcomeEmail($user);

    $rendered = $mailable->render();

    expect($rendered)->toContain('Jane');
    expect($rendered)->toContain('Major');
    expect($rendered)->toContain('eRegister');
});

it('is queued only once per registration', function () {
    Mail::fake();

    $this->post(route('register.store'), [
        'first_name' => 'Single',
        'last_name' => 'Send',
        'email' => 'single@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    Mail::assertQueued(WelcomeEmail::class, 1);
});
