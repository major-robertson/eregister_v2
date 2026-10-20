<?php

namespace App\Domains\Lien\Livewire;

use App\Domains\Lien\Enums\PartyRole;
use App\Domains\Lien\Models\LienParty;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Support\PartyAddress;
use App\Domains\Lien\Support\ZipStateRanges;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;

class PartyManager extends Component
{
    public LienProject $project;

    public bool $showModal = false;

    public ?int $editingPartyId = null;

    // Form fields
    public string $role = 'customer';

    public string $name = '';

    public ?string $company_name = null;

    public ?string $address1 = null;

    public ?string $address2 = null;

    public ?string $city = null;

    public ?string $state = null;

    public ?string $county = null;

    public ?string $zip = null;

    public ?string $email = null;

    public ?string $phone = null;

    // "This is really their mailing address" for a general contractor,
    // subcontractor or customer at the jobsite address.
    public bool $sameAsJobsiteConfirmed = false;

    // "This address is correct as typed" for a ZIP code that seems to be in
    // another state.
    public bool $addressConfirmed = false;

    public function mount(LienProject $project): void
    {
        $this->project = $project;
    }

    public function rules(): array
    {
        return [
            'role' => ['required', Rule::enum(PartyRole::class)],
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'address1' => ['required', 'string', 'max:255'],
            'address2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:2'],
            'county' => ['nullable', 'string', 'max:255'],
            'zip' => ['required', 'string', 'max:10'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
        ];
    }

    /**
     * Friendlier field names for validation messages.
     */
    protected function validationAttributes(): array
    {
        return [
            'address1' => 'street address',
            'city' => 'city',
            'state' => 'state',
            'zip' => 'ZIP code',
        ];
    }

    public function openModal(?int $partyId = null): void
    {
        $this->resetForm();

        if ($partyId) {
            $party = $this->project->parties()->findOrFail($partyId);
            $this->editingPartyId = $party->id;
            $this->role = $party->role->value;
            $this->name = $party->name;
            $this->company_name = $party->company_name;
            $this->address1 = $party->address1;
            $this->address2 = $party->address2;
            $this->city = $party->city;
            $this->state = $party->state;
            $this->county = $party->county;
            $this->zip = $party->zip;
            $this->email = $party->email;
            $this->phone = $party->phone;
            $this->sameAsJobsiteConfirmed = (bool) $party->address_same_as_jobsite_confirmed;
            $this->addressConfirmed = (bool) $party->address_confirmed_by_customer;
        }

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function save(): void
    {
        $this->validate();

        $zipMismatch = ZipStateRanges::mismatchMessage($this->zip, $this->state) !== null;
        $atJobsite = $this->addressIsJobsite();
        $unconfirmed = [];

        if ($zipMismatch && ! $this->addressConfirmed) {
            $unconfirmed['addressConfirmed'] = 'Fix the ZIP code or the state. If the address is right, tick the box.';
        }

        if ($atJobsite && ! $this->sameAsJobsiteConfirmed) {
            $unconfirmed['sameAsJobsiteConfirmed'] = 'This is the jobsite address. Enter their mailing address. If this really is their mailing address, tick the box.';
        }

        if ($unconfirmed !== []) {
            throw ValidationException::withMessages($unconfirmed);
        }

        $data = [
            'role' => $this->role,
            'name' => $this->name,
            'company_name' => $this->company_name,
            'address1' => $this->address1,
            'address2' => $this->address2,
            'city' => $this->city,
            'state' => $this->state ? strtoupper($this->state) : null,
            'county' => $this->county,
            'zip' => $this->zip,
            'address_same_as_jobsite_confirmed' => $atJobsite,
            'address_confirmed_by_customer' => $zipMismatch ? true : null,
            'email' => $this->email,
            'phone' => $this->phone,
        ];

        if ($this->editingPartyId) {
            $party = $this->project->parties()->findOrFail($this->editingPartyId);
            $party->update($data);
        } else {
            $data['business_id'] = $this->project->business_id;
            $data['project_id'] = $this->project->id;
            LienParty::create($data);
        }

        $this->closeModal();
        $this->dispatch('party-saved');
    }

    public function deleteParty(int $partyId): void
    {
        $party = $this->project->parties()->findOrFail($partyId);
        $party->delete();

        $this->dispatch('party-deleted');
    }

    public function prefillClaimant(): void
    {
        $business = $this->project->business;
        $this->role = 'claimant';
        $this->name = $business->legal_name ?? $business->name ?? '';
        $this->company_name = $business->dba_name;

        $address = $business->business_address ?? [];
        $this->address1 = $address['address1'] ?? null;
        $this->address2 = $address['address2'] ?? null;
        $this->city = $address['city'] ?? null;
        $this->state = $address['state'] ?? null;
        $this->zip = $address['zip'] ?? null;
    }

    private function resetForm(): void
    {
        $this->editingPartyId = null;
        $this->role = 'customer';
        $this->name = '';
        $this->company_name = null;
        $this->address1 = null;
        $this->address2 = null;
        $this->city = null;
        $this->state = null;
        $this->county = null;
        $this->zip = null;
        $this->email = null;
        $this->phone = null;
        $this->sameAsJobsiteConfirmed = false;
        $this->addressConfirmed = false;
        $this->resetValidation();
    }

    /**
     * Whether the form holds a general contractor, subcontractor or customer
     * whose address is the jobsite address.
     */
    private function addressIsJobsite(): bool
    {
        return $this->project->mailingAddressMustDifferFromJobsite(PartyRole::tryFrom($this->role))
            && PartyAddress::sameAsJobsite($this->project, $this->address1, $this->city, $this->zip);
    }

    /**
     * Google Places pick for the party modal's street-address input
     * (see livewire.lien._places-autocomplete).
     *
     * @param  array<string, mixed>  $addressData
     */
    public function updateAddressFromAutocomplete(array $addressData): void
    {
        $this->address1 = $addressData['line1'] ?? null;
        $this->city = $addressData['city'] ?? null;
        $this->state = $addressData['state'] ?? null;
        $this->county = $addressData['county'] ?? null;
        $this->zip = $addressData['zip'] ?? null;
    }

    #[On('party-saved')]
    #[On('party-deleted')]
    public function refreshParties(): void
    {
        $this->project->refresh();
    }

    public function render(): View
    {
        return view('livewire.lien.party-manager', [
            'parties' => $this->project->parties,
            'partyRoles' => PartyRole::cases(),
            'atJobsite' => $this->showModal && $this->addressIsJobsite(),
            'zipWarning' => $this->showModal ? ZipStateRanges::mismatchMessage($this->zip, $this->state) : null,
        ]);
    }
}
