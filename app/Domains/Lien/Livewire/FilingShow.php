<?php

namespace App\Domains\Lien\Livewire;

use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFiling;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithFileUploads;

class FilingShow extends Component
{
    use WithFileUploads;

    public LienFiling $filing;

    public ?string $tracking_number = null;

    public $proofFile = null;

    public function mount(LienFiling $filing): void
    {
        Gate::authorize('view', $filing);
        $this->filing = $filing;
        $this->tracking_number = $filing->mailing_tracking_number;
    }

    public function markMailed(): void
    {
        $this->validate([
            'tracking_number' => ['nullable', 'string', 'max:100'],
        ]);

        if ($this->filing->status === FilingStatus::Paid) {
            $this->filing->transitionTo(FilingStatus::Mailed, [
                'tracking_number' => $this->tracking_number,
            ]);
        }

        session()->flash('message', 'Filing marked as mailed.');
    }

    public function markRecorded(): void
    {
        if ($this->filing->status === FilingStatus::Mailed) {
            $this->filing->transitionTo(FilingStatus::Recorded);
        }

        session()->flash('message', 'Filing marked as recorded.');
    }

    public function markComplete(): void
    {
        if (in_array($this->filing->status, [FilingStatus::Paid, FilingStatus::Mailed, FilingStatus::Recorded])) {
            $this->filing->transitionTo(FilingStatus::Complete);
        }

        session()->flash('message', 'Filing marked as complete.');
    }

    public function uploadProof(): void
    {
        $this->validate([
            'proofFile' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        $this->filing->addMedia($this->proofFile->getRealPath())
            ->usingFileName($this->proofFile->getClientOriginalName())
            ->toMediaCollection('proofs');

        $this->proofFile = null;

        session()->flash('message', 'Proof document uploaded.');
    }

    public function render(): View
    {
        $this->filing->load(['project', 'documentType', 'recipients', 'events' => fn ($q) => $q->latest(), 'payment']);

        return view('livewire.lien.filing-show', [
            'events' => $this->filing->events,
            'recipients' => $this->filing->recipients,
            'proofs' => $this->filing->getMedia('proofs'),
            'canDownload' => $this->filing->isPaid() && $this->filing->getFirstMedia('generated'),
        ])->layout('layouts.lien', ['title' => $this->filing->documentType->name]);
    }
}
