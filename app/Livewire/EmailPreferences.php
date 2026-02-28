<?php

namespace App\Livewire;

use App\Models\EmailUnsubscribe;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class EmailPreferences extends Component
{
    public User $user;

    public bool $unsubscribedFromAll = false;

    /** @var array<string, bool> */
    public array $categories = [];

    public bool $saved = false;

    public function mount(User $user): void
    {
        $this->user = $user;

        $this->unsubscribedFromAll = $this->user->unsubscribed_from_all_emails_at !== null;

        foreach (EmailUnsubscribe::$categories as $key => $label) {
            $this->categories[$key] = EmailUnsubscribe::where('user_id', $this->user->id)
                ->where('category', $key)
                ->exists();
        }
    }

    public function toggleCategory(string $category): void
    {
        $this->saved = false;

        if ($this->categories[$category]) {
            EmailUnsubscribe::unsubscribe($this->user, $category);
        } else {
            EmailUnsubscribe::resubscribe($this->user, $category);
        }

        $this->saved = true;
    }

    public function toggleUnsubscribeAll(): void
    {
        $this->saved = false;

        if ($this->unsubscribedFromAll) {
            EmailUnsubscribe::unsubscribeFromAll($this->user);

            foreach (array_keys($this->categories) as $key) {
                $this->categories[$key] = true;
                EmailUnsubscribe::unsubscribe($this->user, $key);
            }
        } else {
            EmailUnsubscribe::resubscribeToAll($this->user);

            foreach (array_keys($this->categories) as $key) {
                $this->categories[$key] = false;
            }
        }

        $this->saved = true;
    }

    public function render(): View
    {
        return view('livewire.email-preferences', [
            'categoryLabels' => EmailUnsubscribe::$categories,
        ]);
    }
}
