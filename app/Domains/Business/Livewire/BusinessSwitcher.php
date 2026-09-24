<?php

namespace App\Domains\Business\Livewire;

use App\Concerns\ResolvesMarketingLead;
use App\Domains\Business\Actions\CompleteBusinessOnboarding;
use App\Domains\Business\Models\Business;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class BusinessSwitcher extends Component
{
    use ResolvesMarketingLead;

    public ?int $selectedBusinessId = null;

    /** @var \Illuminate\Database\Eloquent\Collection<int, Business> */
    public $businesses;

    public string $newBusinessName = '';

    /**
     * A waiver sign-up sets the business up here, name and address on one
     * screen, and goes straight to the waiver wizard. Every other product
     * keeps the two screens: the name here, the address on the onboarding
     * wizard, which lien sign-ups continue from into lien onboarding.
     */
    public bool $oneScreenSetup = false;

    /** One-screen setup only: the sign-up conversion scripts, fired once. */
    public bool $justRegistered = false;

    /** One-screen setup only. Same shape as OnboardingWizard's. */
    public array $businessAddress = [
        'line1' => '',
        'line2' => '',
        'city' => '',
        'state' => '',
        'zip' => '',
        'place_id' => null,
        'formatted_address' => null,
        'lat' => null,
        'lng' => null,
        'county' => null,
        'country' => null,
    ];

    public function mount(): void
    {
        $this->loadBusinesses();
        $this->selectedBusinessId = session('current_business_id');

        // Pre-fill business name from marketing lead for new signups
        if ($this->businesses->isEmpty()) {
            $lead = $this->resolveLeadForPrefill();
            if ($lead) {
                $this->newBusinessName = $lead->business_name ?? '';
            }

            $this->oneScreenSetup = Auth::user()->signedUpFromWaivers();

            if ($this->oneScreenSetup) {
                // The address screen, which normally fires the sign-up
                // conversion, is skipped. Pulled, not read, so a refresh
                // never fires it twice.
                $this->justRegistered = (bool) session()->pull('just_registered');

                if ($lead) {
                    $this->businessAddress['line1'] = $lead->mailing_address ?? '';
                    $this->businessAddress['line2'] = $lead->mailing_address_2 ?? '';
                    $this->businessAddress['city'] = $lead->mailing_city ?? '';
                    $this->businessAddress['state'] = $lead->mailing_state ?? '';
                    $this->businessAddress['zip'] = $lead->mailing_zip ?? '';
                }
            }
        }
    }

    /**
     * Google Places pick for the one-screen setup's street-address input
     * (see livewire.lien._places-autocomplete). Geo fields come from the
     * server-side geocode on save.
     *
     * @param  array<string, mixed>  $addressComponents
     */
    public function updateAddressFromAutocomplete(array $addressComponents): void
    {
        $this->businessAddress = [
            ...$this->businessAddress,
            'line1' => $addressComponents['line1'] ?? '',
            'city' => $addressComponents['city'] ?? '',
            'state' => strtoupper((string) ($addressComponents['state'] ?? '')),
            'zip' => $addressComponents['zip'] ?? '',
            'county' => $addressComponents['county'] ?? null,
        ];
    }

    public function loadBusinesses(): void
    {
        $this->businesses = Auth::user()->businesses()->orderBy('name')->get();
    }

    public function selectBusiness(int $businessId): mixed
    {
        $business = Auth::user()->businesses()->find($businessId);

        if (! $business) {
            $this->addError('business', 'Business not found.');

            return null;
        }

        session(['current_business_id' => $business->id]);
        $this->selectedBusinessId = $business->id;

        if ($business->isOnboardingComplete()) {
            return $this->redirect(route('dashboard'), navigate: true);
        }

        return $this->redirect(route('portal.onboarding'), navigate: true);
    }

    public function createBusiness(): mixed
    {
        $rules = [
            'newBusinessName' => ['required', 'string', 'min:2', 'max:120'],
        ];
        $attributes = [];

        if ($this->oneScreenSetup) {
            // Same rules as the onboarding wizard's address screen, checked
            // together with the name so the screen reports every gap at once.
            $rules += [
                'businessAddress.line1' => ['required', 'string', 'max:100'],
                'businessAddress.line2' => ['nullable', 'string', 'max:100'],
                'businessAddress.city' => ['required', 'string', 'max:50'],
                'businessAddress.state' => ['required', 'string', 'size:2'],
                'businessAddress.zip' => ['required', 'string', 'max:10'],
            ];
            $attributes = [
                'newBusinessName' => 'business name',
                'businessAddress.line1' => 'street address',
                'businessAddress.city' => 'city',
                'businessAddress.state' => 'state',
                'businessAddress.zip' => 'ZIP code',
            ];
        }

        $this->validate($rules, [], $attributes);

        $business = Business::create([
            'name' => $this->newBusinessName,
        ]);

        Auth::user()->businesses()->attach($business->id, ['role' => 'owner']);

        session(['current_business_id' => $business->id]);

        if (! $this->oneScreenSetup) {
            return $this->redirect(route('portal.onboarding'), navigate: true);
        }

        // What the onboarding wizard would do next, done here: legal name,
        // address, onboarding complete. Then the wizard, where
        // OnboardingWizard::complete() sends a waiver sign-up too.
        $business->update(['legal_name' => $this->newBusinessName]);
        app(CompleteBusinessOnboarding::class)->execute($business, $this->businessAddress);

        return $this->redirect(route('lien.waivers.create'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.business.business-switcher')
            ->layout('layouts.minimal', ['title' => 'Get Started']);
    }
}
