<?php

use App\Livewire\ContactForm;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

it('displays the contact page', function () {
    $this->get(route('contact'))
        ->assertSuccessful()
        ->assertSeeLivewire(ContactForm::class);
});

it('requires name and email and message', function () {
    Livewire::test(ContactForm::class)
        ->call('submit')
        ->assertHasErrors(['name', 'email', 'message']);
});

it('validates email format', function () {
    Livewire::test(ContactForm::class)
        ->set('name', 'John Doe')
        ->set('email', 'not-an-email')
        ->set('message', 'This is a test message.')
        ->call('submit')
        ->assertHasErrors(['email']);
});

it('validates message minimum length', function () {
    Livewire::test(ContactForm::class)
        ->set('name', 'John Doe')
        ->set('email', 'john@example.com')
        ->set('message', 'Short')
        ->call('submit')
        ->assertHasErrors(['message']);
});

it('submits the contact form successfully', function () {
    Mail::fake();

    Livewire::test(ContactForm::class)
        ->set('name', 'John Doe')
        ->set('business_name', 'Acme Corp')
        ->set('email', 'john@example.com')
        ->set('message', 'This is a test message from the contact form.')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('submitted', true)
        ->assertSet('name', '')
        ->assertSet('email', '')
        ->assertSet('message', '');

    Mail::assertSentCount(1);
});

it('allows optional business name', function () {
    Mail::fake();

    Livewire::test(ContactForm::class)
        ->set('name', 'John Doe')
        ->set('email', 'john@example.com')
        ->set('message', 'This is a test message without a business name.')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('submitted', true);

    Mail::assertSentCount(1);
});
