<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class ContactForm extends Component
{
    public string $name = '';

    public string $business_name = '';

    public string $email = '';

    public string $message = '';

    public bool $submitted = false;

    /**
     * @return array<string, array<string>>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'business_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
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

    public function submit(): void
    {
        $validated = $this->validate();

        // Send email notification
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
        $this->reset(['name', 'business_name', 'email', 'message']);
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
