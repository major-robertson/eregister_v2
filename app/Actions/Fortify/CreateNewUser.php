<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        $user = User::create([
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'email' => $input['email'],
            'password' => $input['password'],
            ...$this->getSignupAttribution(),
        ]);

        $this->clearSignupAttributionSession();

        return $user;
    }

    /**
     * Get signup attribution data from session and request.
     *
     * @return array<string, string|null>
     */
    protected function getSignupAttribution(): array
    {
        return [
            'signup_landing_path' => session('signup_landing_path'),
            'signup_landing_url' => session('signup_landing_url'),
            'signup_referrer' => session('signup_referrer'),
            'signup_utm_source' => session('signup_utm_source'),
            'signup_utm_medium' => session('signup_utm_medium'),
            'signup_utm_campaign' => session('signup_utm_campaign'),
            'signup_utm_term' => session('signup_utm_term'),
            'signup_utm_content' => session('signup_utm_content'),
            'signup_ip' => request()->ip(),
            'signup_user_agent' => request()->userAgent(),
        ];
    }

    /**
     * Clear signup attribution data from session after user creation.
     */
    protected function clearSignupAttributionSession(): void
    {
        session()->forget([
            'signup_landing_path',
            'signup_landing_url',
            'signup_referrer',
            'signup_utm_source',
            'signup_utm_medium',
            'signup_utm_campaign',
            'signup_utm_term',
            'signup_utm_content',
        ]);
    }
}
