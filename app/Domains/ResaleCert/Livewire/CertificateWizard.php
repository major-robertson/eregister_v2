<?php

namespace App\Domains\ResaleCert\Livewire;

use App\Domains\ResaleCert\Livewire\Concerns\ResolvesResaleContext;
use App\Domains\ResaleCert\Models\ResaleStateRule;
use App\Domains\ResaleCert\Models\ResaleVendor;
use App\Domains\ResaleCert\Services\CertificateGenerator;
use App\Domains\ResaleCert\Services\MinimumFormsService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Certificate generation wizard.
 *
 * Step 1: pick a vendor + the states to cover. A state is selectable when
 * the business is registered there, or the state accepts out-of-state tax
 * ids, or a uniform form (SST / opted-in MTC) covers it. MTC-only states
 * hide entirely until MTC is enabled in settings.
 *
 * Step 2: review the minimum form set (SST > MTC > individual), optionally
 * add individual forms for uniform-covered states, and generate. Unchecking
 * a required form blocks generation until every selected state is covered.
 */
class CertificateWizard extends Component
{
    use ResolvesResaleContext;

    public int $step = 1;

    #[Url('vendor_id')]
    public string $vendorId = '';

    /** @var list<string> */
    public array $selectedStates = [];

    /**
     * Checked state per form key (e.g. 'SST', 'MTC', 'TX'), populated when
     * entering step 2.
     *
     * @var array<string, bool>
     */
    public array $checkedForms = [];

    /** @var list<array<string, mixed>> */
    public array $minimumForms = [];

    /** @var list<array<string, mixed>> */
    public array $optionalForms = [];

    public function mount(): void
    {
        if (! $this->resolveBusiness() || ! $this->requireCompleteProfile()) {
            return;
        }

        if ($this->vendorId !== '' && ! ResaleVendor::whereKey($this->vendorId)->exists()) {
            $this->vendorId = '';
        }
    }

    /**
     * @return \Illuminate\Support\Collection<int, ResaleVendor>
     */
    #[Computed]
    public function vendors(): \Illuminate\Support\Collection
    {
        return ResaleVendor::query()->orderBy('legal_name')->get();
    }

    /**
     * @return list<string>
     */
    #[Computed]
    public function registeredStateCodes(): array
    {
        return $this->business->resaleTaxRegistrations()->pluck('state_code')->all();
    }

    /**
     * All selectable states with the reason they're available (or locked).
     *
     * @return list<array{code: string, name: string, selectable: bool, registered: bool, reason: string}>
     */
    #[Computed]
    public function stateOptions(): array
    {
        $registered = $this->registeredStateCodes;
        $mtcEnabled = (bool) $this->resaleProfile()?->mtc_enabled;

        return ResaleStateRule::statesOnly()
            ->orderBy('state_name')
            ->get()
            ->map(function (ResaleStateRule $rule) use ($registered, $mtcEnabled) {
                $isRegistered = in_array($rule->state_code, $registered, true);
                $selectable = $isRegistered
                    || $rule->accepts_out_of_state
                    || $rule->accepts_sst
                    || ($rule->accepts_mtc && $mtcEnabled);

                // MTC-only states stay hidden until the MTC opt-in.
                $mtcOnly = ! $isRegistered
                    && ! $rule->accepts_out_of_state
                    && ! $rule->accepts_sst
                    && $rule->accepts_mtc;

                if ($mtcOnly && ! $mtcEnabled) {
                    return null;
                }

                return [
                    'code' => $rule->state_code,
                    'name' => $rule->state_name,
                    'selectable' => $selectable,
                    'registered' => $isRegistered,
                    'reason' => $selectable ? '' : 'State tax registration required',
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    public function toggleState(string $stateCode): void
    {
        if (in_array($stateCode, $this->selectedStates, true)) {
            $this->selectedStates = array_values(array_diff($this->selectedStates, [$stateCode]));
        } else {
            $this->selectedStates[] = $stateCode;
        }
    }

    public function continueToReview(MinimumFormsService $formsService): void
    {
        $this->validate([
            'vendorId' => ['required', 'exists:resale_vendors,id'],
            'selectedStates' => ['required', 'array', 'min:1'],
            'selectedStates.*' => ['string', 'exists:resale_state_rules,state_code'],
        ], [
            'vendorId.required' => 'Select the vendor this certificate is for.',
            'selectedStates.required' => 'Select at least one state.',
        ]);

        $selectable = collect($this->stateOptions)->where('selectable', true)->pluck('code')->all();
        $invalid = array_diff($this->selectedStates, $selectable);

        if ($invalid !== []) {
            throw ValidationException::withMessages([
                'selectedStates' => 'Some selected states are not available: '.implode(', ', $invalid),
            ]);
        }

        $calculation = $formsService->calculateMinimumForms($this->selectedStates, $this->resaleProfile());

        $this->minimumForms = $calculation['minimum'];
        $this->optionalForms = $calculation['optional'];

        $this->checkedForms = [];

        foreach ($this->minimumForms as $form) {
            $this->checkedForms[$form['state_code']] = true;
        }

        foreach ($this->optionalForms as $form) {
            $this->checkedForms[$form['state_code']] = $this->checkedForms[$form['state_code']] ?? false;
        }

        $this->step = 2;
    }

    public function backToSelection(): void
    {
        $this->step = 1;
    }

    /**
     * States selected in step 1 that no checked form covers.
     *
     * @return list<string>
     */
    #[Computed]
    public function uncoveredStates(): array
    {
        $covered = [];

        foreach ([...$this->minimumForms, ...$this->optionalForms] as $form) {
            if ($this->checkedForms[$form['state_code']] ?? false) {
                $covered = [...$covered, ...$form['covers_states']];
            }
        }

        return array_values(array_diff($this->selectedStates, array_unique($covered)));
    }

    public function generate(CertificateGenerator $generator): void
    {
        // Defense in depth behind the esign.verified route middleware: a
        // stale Livewire call can't apply a signature without verification.
        if (Auth::user()->email_verified_at === null) {
            throw ValidationException::withMessages([
                'generate' => 'Verify your email address before generating signed certificates.',
            ]);
        }

        if (! Auth::user()->currentSignature()->exists()) {
            throw ValidationException::withMessages([
                'generate' => 'Add your e-signature in Settings before generating certificates.',
            ]);
        }

        if ($this->uncoveredStates !== []) {
            throw ValidationException::withMessages([
                'generate' => 'These states are not covered by any selected form: '.implode(', ', $this->uncoveredStates),
            ]);
        }

        $vendor = ResaleVendor::findOrFail($this->vendorId);

        $forms = collect([...$this->minimumForms, ...$this->optionalForms])
            ->filter(fn ($form) => $this->checkedForms[$form['state_code']] ?? false)
            ->map(fn ($form) => [
                'state_code' => $form['state_code'],
                'covers_states' => $form['covers_states'],
                'is_blanket' => true,
                'item_description' => null,
            ])
            ->values()
            ->all();

        if ($forms === []) {
            throw ValidationException::withMessages([
                'generate' => 'Select at least one form to generate.',
            ]);
        }

        $generated = $generator->generate($this->business, $vendor, Auth::user(), $forms);

        session()->flash('success', count($generated).' certificate(s) generated successfully.');

        $this->redirect(route('resale-cert.certificates.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.resale-cert.certificate-wizard')
            ->layout('components.layouts.portal', ['title' => 'Generate Certificates']);
    }
}
