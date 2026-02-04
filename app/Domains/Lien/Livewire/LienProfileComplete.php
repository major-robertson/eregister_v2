<?php

namespace App\Domains\Lien\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LienProfileComplete extends Component
{
    /**
     * Whether this is a continuous flow from business onboarding (from liens signup, first business).
     * Determines if we show 4 unified dots or standalone display.
     */
    public bool $isContinuousFlow = false;

    public function mount(): void
    {
        $user = Auth::user();

        // Determine if this is continuous flow (from liens signup, first business)
        $this->isContinuousFlow = $user->signup_landing_path === '/liens'
            && $user->businesses()->count() === 1;
    }

    public function proceed(): mixed
    {
        return $this->redirect(route('lien.projects.create'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.lien.lien-profile-complete')
            ->layout('layouts.minimal', ['title' => 'Profile Complete']);
    }
}
