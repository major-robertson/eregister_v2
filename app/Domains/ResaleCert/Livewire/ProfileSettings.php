<?php

namespace App\Domains\ResaleCert\Livewire;

use App\Domains\ResaleCert\Livewire\Concerns\ResolvesResaleContext;
use App\Domains\ResaleCert\Models\ResaleProfile;
use App\Domains\ResaleCert\Models\ResaleStateRule;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Resale profile settings: certificate details, tax registrations (with
 * home-state selection and per-state expiration overrides), the MTC
 * opt-in, and the signature.
 */
class ProfileSettings extends Component
{
    use ResolvesResaleContext;

    public string $products_description = '';

    public string $contact_email = '';

    public string $contact_phone = '';

    public string $signer_title = '';

    public bool $mtc_enabled = false;

    public string $default_expiration_rule = 'end_of_current_year';

    /** @var list<array{state_code: string, tax_id: string, expiration_rule: string}> */
    public array $registrations = [];

    public int $homeStateIndex = 0;

    public function mount(): void
    {
        if (! $this->resolveBusiness() || ! $this->requireCompleteProfile()) {
            return;
        }

        $profile = $this->business->resaleProfile;
        $signer = $this->business->getResponsiblePersonForUser(Auth::id());

        $this->products_description = $profile->products_description ?? '';
        $this->contact_email = $profile->contact_email ?? '';
        $this->contact_phone = $profile->contact_phone ?? '';
        $this->signer_title = $signer['title'] ?? '';
        $this->mtc_enabled = $profile->mtc_enabled;
        $this->default_expiration_rule = $profile->default_expiration_rule;

        $stateRules = $profile->state_expiration_rules ?? [];

        $this->registrations = $this->business->resaleTaxRegistrations()
            ->orderByDesc('is_home_state')
            ->orderBy('state_code')
            ->get()
            ->map(fn ($registration) => [
                'state_code' => $registration->state_code,
                'tax_id' => $registration->tax_id,
                'expiration_rule' => $stateRules[$registration->state_code] ?? '',
            ])
            ->all();

        $this->homeStateIndex = 0;
    }

    /**
     * @return \Illuminate\Support\Collection<int, ResaleStateRule>
     */
    #[Computed]
    public function states(): \Illuminate\Support\Collection
    {
        return ResaleStateRule::statesOnly()->orderBy('state_name')->get();
    }

    /**
     * @return array<string, string>
     */
    #[Computed]
    public function expirationRules(): array
    {
        return ResaleProfile::EXPIRATION_RULES;
    }

    public function addRegistration(): void
    {
        $this->registrations[] = ['state_code' => '', 'tax_id' => '', 'expiration_rule' => ''];
    }

    public function removeRegistration(int $index): void
    {
        if (! isset($this->registrations[$index]) || $index === $this->homeStateIndex) {
            return;
        }

        unset($this->registrations[$index]);
        $this->registrations = array_values($this->registrations);

        if ($this->homeStateIndex >= count($this->registrations)) {
            $this->homeStateIndex = 0;
        }
    }

    public function save(): void
    {
        $this->validate([
            'products_description' => ['required', 'string', 'min:10', 'max:100'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['required', 'string', 'regex:/^(\(\d{3}\) \d{3}-\d{4}|\d{10})$/'],
            'signer_title' => ['required', 'string', 'max:255'],
            'default_expiration_rule' => ['required', Rule::in(array_keys(ResaleProfile::EXPIRATION_RULES))],
            'registrations' => ['required', 'array', 'min:1'],
            'registrations.*.state_code' => [
                'required',
                'string',
                'size:2',
                Rule::exists('resale_state_rules', 'state_code'),
            ],
            'registrations.*.tax_id' => ['required', 'string', 'max:255'],
            'registrations.*.expiration_rule' => [
                'nullable',
                Rule::in(array_keys(ResaleProfile::EXPIRATION_RULES)),
            ],
        ]);

        $codes = array_column($this->registrations, 'state_code');

        if (count($codes) !== count(array_unique($codes))) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'registrations' => 'Each state can only be registered once.',
            ]);
        }

        if (! isset($this->registrations[$this->homeStateIndex])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'registrations' => 'Select which state is your home state.',
            ]);
        }

        DB::transaction(function () {
            $profile = $this->business->resaleProfile;

            $stateRules = [];

            foreach ($this->registrations as $registration) {
                if (filled($registration['expiration_rule'])) {
                    $stateRules[$registration['state_code']] = $registration['expiration_rule'];
                }
            }

            $profile->update([
                'products_description' => $this->products_description,
                'contact_email' => $this->contact_email,
                'contact_phone' => $this->contact_phone,
                'mtc_enabled' => $this->mtc_enabled,
                'default_expiration_rule' => $this->default_expiration_rule,
                'state_expiration_rules' => $stateRules ?: null,
            ]);

            $user = Auth::user();
            $this->business->setResponsiblePersonForUser($user->id, $user->name, $this->signer_title);

            $this->business->resaleTaxRegistrations()->delete();

            foreach (array_values($this->registrations) as $index => $registration) {
                $this->business->resaleTaxRegistrations()->create([
                    'state_code' => $registration['state_code'],
                    'tax_id' => $registration['tax_id'],
                    'is_home_state' => $index === $this->homeStateIndex,
                ]);
            }
        });

        Flux::toast(text: 'Resale profile saved.', variant: 'success');
    }

    public function render(): View
    {
        return view('livewire.resale-cert.profile-settings')
            ->layout('components.layouts.portal', ['title' => 'Resale Certificate Settings']);
    }
}
