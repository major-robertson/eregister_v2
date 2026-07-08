<?php

namespace App\Domains\Esign\Livewire;

use App\Domains\Esign\Enums\SignatureRequestStatus;
use App\Domains\Esign\Models\SignatureRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * A signer's archive: every completed signing session tied to their account
 * (guest sessions are claimed by email at login). This is the recipient-side
 * "track your waivers" page free accounts land on.
 */
class MyDocuments extends Component
{
    #[Computed]
    public function requests(): Collection
    {
        return SignatureRequest::query()
            ->with('documents')
            ->where('status', SignatureRequestStatus::Completed)
            ->where(function ($query): void {
                $query->where('signer_user_id', auth()->id())
                    ->orWhere(function ($inner): void {
                        $inner->whereNull('signer_user_id')
                            ->where('signer_email_snapshot', auth()->user()->email);
                    });
            })
            ->latest('completed_at')
            ->get();
    }

    public function render(): View
    {
        return view('livewire.esign.my-documents')
            ->layout('layouts.minimal', ['title' => 'Documents I\'ve Signed']);
    }
}
