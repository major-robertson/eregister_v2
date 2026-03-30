<?php

namespace App\Livewire;

use App\Rules\Recaptcha;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class ContactForm extends Component
{
    public string $name = '';

    public string $business_name = '';

    public string $email = '';

    public string $message = '';

    public string $website = '';

    public string $recaptchaToken = '';

    public bool $submitted = false;

    /**
     * @return array<string, array<int, \Illuminate\Contracts\Validation\ValidationRule|string>>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'business_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'website' => ['prohibited'],
            'recaptchaToken' => [new Recaptcha],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'name.required' => 'Please enter your name.',
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'message.required' => 'Please enter a message.',
            'message.min' => 'Your message must be at least 10 characters.',
        ];
    }

    /**
     * @throws ValidationException
     */
    public function submit(): void
    {
        $this->checkRateLimit();
        RateLimiter::hit($this->rateLimitKey(), 60 * 15);

        $validated = $this->validate();

        Mail::raw(
            "New contact form submission:\n\n".
            "Name: {$validated['name']}\n".
            'Business: '.($validated['business_name'] ?: 'N/A')."\n".
            "Email: {$validated['email']}\n\n".
            "Message:\n{$validated['message']}",
            function ($message) use ($validated) {
                $message->to(config('mail.from.address'))
                    ->replyTo($validated['email'], $validated['name'])
                    ->subject('Contact Form: '.$validated['name']);
            }
        );

        $this->submitted = true;
        $this->reset(['name', 'business_name', 'email', 'message', 'website', 'recaptchaToken']);
    }

    /**
     * @throws ValidationException
     */
    protected function checkRateLimit(): void
    {
        $key = $this->rateLimitKey();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'email' => "Too many contact attempts. Please try again in {$seconds} seconds.",
            ]);
        }
    }

    protected function rateLimitKey(): string
    {
        return 'contact:'.request()->ip();
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
