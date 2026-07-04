<?php

namespace App\Domains\ResaleCert\Livewire;

use App\Domains\ResaleCert\Livewire\Concerns\ResolvesResaleContext;
use App\Domains\ResaleCert\Models\ResaleProfile;
use App\Domains\ResaleCert\Models\ResaleStateRule;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Resale profile setup wizard. Business identity (legal name, EIN, address)
 * already lives on the Business from portal onboarding, so this collects
 * only what certificates additionally need: products description + contact
 * info + signer title, per-state tax registrations, and a drawn signature.
 * Each step persists on Next, so progress survives leaving the page.
 */
class ResaleOnboarding extends Component
{
    use ResolvesResaleContext;

    public int $step = 1;

    public int $totalSteps = 3;

    // Step 1 — certificate details
    public string $products_description = '';

    public string $contact_email = '';

    public string $contact_phone = '';

    public string $signer_title = '';

    // Step 2 — tax registrations (first row is the home state)
    /** @var list<array{state_code: string, tax_id: string}> */
    public array $registrations = [
        ['state_code' => '', 'tax_id' => ''],
    ];

    public function mount(): void
    {
        if (! $this->resolveBusiness()) {
            return;
        }

        $profile = $this->business->resaleProfile;

        if ($profile?->isComplete()) {
            $this->redirect(route('resale-cert.dashboard'));

            return;
        }

        $user = Auth::user();
        $signer = $this->business->getResponsiblePersonForUser($user->id);

        $this->products_description = $profile->products_description ?? '';
        $this->contact_email = $profile->contact_email ?? $user->email;
        $this->contact_phone = $profile->contact_phone ?? ($this->business->phone ?? '');
        $this->signer_title = $signer['title'] ?? '';

        $existing = $this->business->resaleTaxRegistrations()
            ->orderByDesc('is_home_state')
            ->get()
            ->map(fn ($registration) => [
                'state_code' => $registration->state_code,
                'tax_id' => $registration->tax_id,
            ])
            ->all();

        if ($existing !== []) {
            $this->registrations = $existing;
            $this->step = 2;
        } elseif ($profile?->products_description) {
            $this->step = 2;
        }
    }

    /**
     * @return \Illuminate\Support\Collection<int, ResaleStateRule>
     */
    #[Computed]
    public function states(): \Illuminate\Support\Collection
    {
        return ResaleStateRule::statesOnly()->orderBy('state_name')->get();
    }

    #[Computed]
    public function hasSignature(): bool
    {
        return Auth::user()->currentSignature()->exists();
    }

    public function addRegistration(): void
    {
        $this->registrations[] = ['state_code' => '', 'tax_id' => ''];
    }

    public function removeRegistration(int $index): void
    {
        // The first row is the home state and can't be removed.
        if ($index === 0 || ! isset($this->registrations[$index])) {
            return;
        }

        unset($this->registrations[$index]);
        $this->registrations = array_values($this->registrations);
    }

    public function nextStep(): void
    {
        $this->validateStep();
        $this->persistStep();

        if ($this->step < $this->totalSteps) {
            $this->step++;
        }
    }

    public function previousStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function goToStep(int $step): void
    {
        if ($step >= 1 && $step <= $this->totalSteps && $step <= $this->step) {
            $this->step = $step;
        }
    }

    protected function validateStep(): void
    {
        match ($this->step) {
            1 => $this->validate([
                'products_description' => ['required', 'string', 'min:10', 'max:100'],
                'contact_email' => ['required', 'email', 'max:255'],
                'contact_phone' => ['required', 'string', 'regex:/^(\(\d{3}\) \d{3}-\d{4}|\d{10})$/'],
                'signer_title' => ['required', 'string', 'max:255'],
            ], [
                'products_description.required' => 'Describe the products or services you sell — it prints on every certificate.',
                'contact_phone.regex' => 'Enter a 10-digit phone number, e.g. (555) 123-4567.',
                'signer_title.required' => 'Your title (e.g. Owner, Manager) prints next to your signature.',
            ]),
            2 => $this->validateRegistrations(),
            3 => $this->validateSignature(),
        };
    }

    protected function validateRegistrations(): void
    {
        $this->validate([
            'registrations' => ['required', 'array', 'min:1'],
            'registrations.*.state_code' => [
                'required',
                'string',
                'size:2',
                Rule::exists('resale_state_rules', 'state_code'),
            ],
            'registrations.*.tax_id' => ['required', 'string', 'max:255'],
        ], [
            'registrations.*.state_code.required' => 'Select a state for each registration.',
            'registrations.*.tax_id.required' => 'Enter the tax/permit id for each registration.',
        ]);

        $codes = array_column($this->registrations, 'state_code');

        if (count($codes) !== count(array_unique($codes))) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'registrations' => 'Each state can only be registered once.',
            ]);
        }
    }

    protected function validateSignature(): void
    {
        if (! $this->hasSignature) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'signature' => 'Draw and save your electronic signature to continue.',
            ]);
        }
    }

    protected function persistStep(): void
    {
        if ($this->step === 1) {
            ResaleProfile::withoutGlobalScopes()->updateOrCreate(
                ['business_id' => $this->business->id],
                [
                    'products_description' => $this->products_description,
                    'contact_email' => $this->contact_email,
                    'contact_phone' => $this->contact_phone,
                ],
            );

            $user = Auth::user();
            $this->business->setResponsiblePersonForUser($user->id, $user->name, $this->signer_title);
        }

        if ($this->step === 2) {
            $this->syncRegistrations();
        }
    }

    protected function syncRegistrations(): void
    {
        DB::transaction(function () {
            $this->business->resaleTaxRegistrations()->delete();

            foreach (array_values($this->registrations) as $index => $registration) {
                $this->business->resaleTaxRegistrations()->create([
                    'state_code' => $registration['state_code'],
                    'tax_id' => $registration['tax_id'],
                    'is_home_state' => $index === 0,
                ]);
            }
        });
    }

    #[On('resale-signature-saved')]
    public function signatureSaved(): void
    {
        unset($this->hasSignature);
        $this->resetValidation('signature');
    }

    public function finish(): void
    {
        $this->validateSignature();

        $profile = ResaleProfile::withoutGlobalScopes()
            ->where('business_id', $this->business->id)
            ->firstOrFail();

        $profile->update(['completed_at' => now()]);

        session()->flash('success', 'Your resale profile is ready. You can now generate certificates!');

        $this->redirect(route('resale-cert.dashboard'));
    }

    public function render(): View
    {
        return view('livewire.resale-cert.resale-onboarding')
            ->layout('components.layouts.portal', ['title' => 'Resale Profile Setup']);
    }
}
