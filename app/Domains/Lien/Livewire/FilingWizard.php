<?php

namespace App\Domains\Lien\Livewire;

use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Enums\ServiceLevel;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienFilingRecipient;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienProjectDeadline;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class FilingWizard extends Component
{
    use WithFileUploads;

    public LienProject $project;

    public LienProjectDeadline $deadline;

    public LienFiling $filing;

    public int $step = 1;

    public int $totalSteps = 5;

    // Step 3: Filing details
    public ?int $amount_claimed = null;

    public ?string $description_of_work = null;

    // Step 4: Service choice
    public string $service_level = 'self_serve';

    // Step 5: Review
    public bool $disclaimerAccepted = false;

    // File uploads
    public array $attachments = [];

    public function mount(LienProject $project, LienProjectDeadline $deadline): void
    {
        Gate::authorize('view', $project);

        $this->project = $project;
        $this->deadline = $deadline;

        // Find or create draft filing
        $this->filing = $project->filings()
            ->where('project_deadline_id', $deadline->id)
            ->whereIn('status', [FilingStatus::Draft, FilingStatus::AwaitingPayment])
            ->first();

        if (! $this->filing) {
            $this->filing = LienFiling::create([
                'public_id' => Str::ulid()->toBase32(),
                'business_id' => $project->business_id,
                'project_id' => $project->id,
                'document_type_id' => $deadline->document_type_id,
                'project_deadline_id' => $deadline->id,
                'jurisdiction_state' => $project->jobsite_state,
                'jurisdiction_county' => $project->jobsite_county,
                'status' => FilingStatus::Draft,
                'created_by_user_id' => auth()->id(),
            ]);
        }

        // Populate from existing filing
        $this->amount_claimed = $this->filing->amount_claimed_cents ? $this->filing->amount_claimed_cents / 100 : null;
        $this->description_of_work = $this->filing->description_of_work;
        $this->service_level = $this->filing->service_level?->value ?? 'self_serve';
    }

    public function nextStep(): void
    {
        $this->validateStep();

        // Auto-save on each step
        $this->updateFiling();

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

    private function validateStep(): void
    {
        match ($this->step) {
            3 => $this->validate([
                'amount_claimed' => ['required', 'numeric', 'min:0.01'],
                'description_of_work' => ['required', 'string', 'min:10'],
            ]),
            4 => $this->validate([
                'service_level' => ['required', 'in:self_serve,full_service'],
            ]),
            default => null,
        };
    }

    private function updateFiling(): void
    {
        $this->filing->update([
            'amount_claimed_cents' => $this->amount_claimed ? (int) ($this->amount_claimed * 100) : null,
            'description_of_work' => $this->description_of_work,
            'service_level' => $this->service_level,
        ]);
    }

    public function proceedToCheckout(): void
    {
        $this->validate([
            'disclaimerAccepted' => ['accepted'],
        ], [
            'disclaimerAccepted.accepted' => 'You must accept the disclaimer to continue.',
        ]);

        $this->updateFiling();

        // Create recipient records from parties
        $this->createRecipients();

        // Snapshot data
        $this->filing->update([
            'payload_json' => $this->buildPayloadSnapshot(),
            'parties_snapshot_json' => $this->buildPartiesSnapshot(),
        ]);

        // Transition to awaiting payment
        if ($this->filing->status === FilingStatus::Draft) {
            $this->filing->transitionTo(FilingStatus::AwaitingPayment);
        }

        $this->redirect(route('lien.filings.checkout', $this->filing));
    }

    private function createRecipients(): void
    {
        // Clear existing recipients
        $this->filing->recipients()->delete();

        // Add owner as recipient
        $owner = $this->project->ownerParty();
        if ($owner) {
            $this->filing->recipients()->create(
                LienFilingRecipient::fromParty($owner)
            );
        }

        // Add customer if different from owner
        $customer = $this->project->parties()->where('role', 'customer')->first();
        if ($customer && $customer->id !== $owner?->id) {
            $this->filing->recipients()->create(
                LienFilingRecipient::fromParty($customer)
            );
        }
    }

    private function buildPayloadSnapshot(): array
    {
        return [
            'project' => [
                'name' => $this->project->name,
                'jobsite_address' => $this->project->jobsiteAddressLine(),
                'legal_description' => $this->project->legal_description,
                'apn' => $this->project->apn,
            ],
            'filing' => [
                'document_type' => $this->deadline->documentType->name,
                'amount_claimed' => $this->amount_claimed,
                'description_of_work' => $this->description_of_work,
                'service_level' => $this->service_level,
            ],
            'dates' => [
                'first_furnish' => $this->project->first_furnish_date?->toDateString(),
                'last_furnish' => $this->project->last_furnish_date?->toDateString(),
            ],
        ];
    }

    private function buildPartiesSnapshot(): array
    {
        return $this->project->parties->map(fn ($p) => $p->toSnapshot())->toArray();
    }

    public function render(): View
    {
        $pricing = config('lien.pricing.'.$this->deadline->documentType->slug, [
            'self_serve' => 4900,
            'full_service' => 9900,
        ]);

        return view('livewire.lien.filing-wizard', [
            'parties' => $this->project->parties,
            'documentType' => $this->deadline->documentType,
            'pricing' => $pricing,
            'serviceLevels' => ServiceLevel::cases(),
        ])->layout('layouts.lien', ['title' => 'Create '.$this->deadline->documentType->name]);
    }
}
