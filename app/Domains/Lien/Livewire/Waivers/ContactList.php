<?php

namespace App\Domains\Lien\Livewire\Waivers;

use App\Domains\Lien\Models\LienContact;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * The waiver contact directory: the GCs, subs, suppliers, and customers a
 * business exchanges lien waivers with. Reusable across projects and waivers;
 * contacts can also be added inline from the waiver wizard. Queries are pinned
 * to the current business by LienContact's BelongsToBusiness global scope.
 */
class ContactList extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function deleteContact(int $contactId): void
    {
        $contact = LienContact::findOrFail($contactId);

        // A contact is snapshotted onto each waiver at creation, so deleting one
        // never rewrites history, but keep the directory link intact while it's
        // still referenced so the "N waivers" view stays meaningful.
        if ($contact->waivers()->exists()) {
            Flux::toast(
                text: 'This contact is used on existing waivers. It stays linked for your records; remove those waivers first to delete it.',
                variant: 'warning',
            );

            return;
        }

        $contact->delete();

        Flux::toast(text: 'Contact deleted.', variant: 'success');
    }

    public function render(): View
    {
        $contacts = LienContact::query()
            ->withCount('waivers')
            ->when($this->search !== '', fn ($query) => $query->where(fn ($q) => $q
                ->where('company_name', 'like', "%{$this->search}%")
                ->orWhere('contact_name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")
            ))
            ->orderBy('company_name')
            ->paginate(15);

        return view('livewire.lien.waivers.contact-list', compact('contacts'))
            ->layout('components.layouts.portal', ['title' => 'Waiver Contacts']);
    }
}
