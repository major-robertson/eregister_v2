<?php

use App\Domains\Business\Models\Business;
use App\Mail\TeamInvitation;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Team')] class extends Component {
    public ?Business $business = null;

    public string $inviteEmail = '';

    public string $inviteRole = 'member';

    public function mount(): void
    {
        $this->business = Auth::user()->currentBusiness();

        abort_unless((bool) $this->business, 404);

        Gate::authorize('manageMembers', $this->business);
    }

    /**
     * Re-check on every mutating action — Livewire updates bypass mount().
     */
    private function authorizeManage(): void
    {
        Gate::authorize('manageMembers', $this->business);
    }

    public function sendInvite(): void
    {
        $this->authorizeManage();

        // An un-onboarded business would funnel a member-role invitee into
        // the onboarding wizard, which they aren't authorized to run.
        if (! $this->business->isOnboardingComplete()) {
            $this->addError('inviteEmail', __('Finish setting up your business before inviting teammates.'));

            return;
        }

        $this->validate([
            'inviteEmail' => ['required', 'email:rfc', 'max:255'],
            'inviteRole' => ['required', 'in:admin,member'],
        ]);

        $email = strtolower(trim($this->inviteEmail));

        if ($this->business->users()->whereRaw('lower(email) = ?', [$email])->exists()) {
            $this->addError('inviteEmail', __('That person is already a member of this business.'));

            return;
        }

        if ($this->business->invitations()->where('email', $email)->exists()) {
            $this->addError('inviteEmail', __('An invitation is already pending for that email.'));

            return;
        }

        $invitation = $this->business->invitations()->create([
            'email' => $email,
            'role' => $this->inviteRole,
            'invited_by_user_id' => Auth::id(),
            'expires_at' => now()->addDays(7),
        ]);

        Mail::to($email)->queue(new TeamInvitation($invitation));

        $this->reset('inviteEmail', 'inviteRole');

        Flux::toast(text: "Invitation sent to {$email}.", variant: 'success');
    }

    public function resendInvite(int $invitationId): void
    {
        $this->authorizeManage();

        $invitation = $this->business->invitations()->findOrFail($invitationId);
        $invitation->update(['expires_at' => now()->addDays(7)]);

        Mail::to($invitation->email)->queue(new TeamInvitation($invitation));

        Flux::toast(text: "Invitation re-sent to {$invitation->email}.", variant: 'success');
    }

    public function revokeInvite(int $invitationId): void
    {
        $this->authorizeManage();

        $this->business->invitations()->findOrFail($invitationId)->delete();

        Flux::toast(text: __('Invitation revoked.'), variant: 'success');
    }

    public function changeRole(int $userId, string $role): void
    {
        $this->authorizeManage();

        abort_unless(in_array($role, ['admin', 'member'], true), 422);
        abort_if($userId === Auth::id(), 403, 'You cannot change your own role.');

        $member = $this->business->users()->findOrFail($userId);

        abort_if($member->pivot->role === 'owner', 403, "The owner's role cannot be changed.");

        $this->business->users()->updateExistingPivot($userId, ['role' => $role]);

        Flux::toast(text: "{$member->name} is now ".($role === 'admin' ? 'an admin' : 'a member').'.', variant: 'success');
    }

    public function removeMember(int $userId): void
    {
        $this->authorizeManage();

        abort_if($userId === Auth::id(), 403, 'You cannot remove yourself.');

        $member = $this->business->users()->findOrFail($userId);

        abort_if($member->pivot->role === 'owner', 403, 'The owner cannot be removed.');

        $this->business->users()->detach($userId);

        Flux::toast(text: "{$member->name} was removed from the business.", variant: 'success');
    }

    public function members()
    {
        return $this->business->users()->orderBy('business_user.created_at')->get();
    }

    public function pendingInvitations()
    {
        return $this->business->invitations()->orderBy('created_at')->get();
    }

    public function roleBadgeColor(string $role): string
    {
        return match ($role) {
            'owner' => 'amber',
            'admin' => 'blue',
            default => 'zinc',
        };
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Team Settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Team')"
        :subheading="__('Invite teammates and manage their access to this business')">
        <div class="my-6 w-full space-y-8">
            <form wire:submit="sendInvite" class="space-y-4">
                <flux:heading size="sm">{{ __('Invite a teammate') }}</flux:heading>

                <flux:input wire:model="inviteEmail" :label="__('Email address')" type="email"
                    placeholder="teammate@example.com" />

                <flux:select wire:model="inviteRole" :label="__('Role')">
                    <option value="member">{{ __('Member — can work in the business') }}</option>
                    <option value="admin">{{ __('Admin — can also edit settings and manage the team') }}</option>
                </flux:select>

                <div class="flex items-center gap-4">
                    <flux:button variant="primary" type="submit">
                        {{ __('Send invitation') }}
                    </flux:button>
                </div>
            </form>

            <flux:separator />

            <div class="space-y-3">
                <flux:heading size="sm">{{ __('Members') }}</flux:heading>

                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Member') }}</flux:table.column>
                        <flux:table.column>{{ __('Role') }}</flux:table.column>
                        <flux:table.column align="end"><span class="sr-only">{{ __('Actions') }}</span></flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach ($this->members() as $member)
                            <flux:table.row wire:key="member-{{ $member->id }}">
                                <flux:table.cell>
                                    <div class="font-medium text-text-primary">{{ $member->name }}</div>
                                    <div class="text-xs text-zinc-500">{{ $member->email }}</div>
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if ($member->pivot->role === 'owner' || $member->id === auth()->id())
                                        <flux:badge size="sm" :color="$this->roleBadgeColor($member->pivot->role)">
                                            {{ ucfirst($member->pivot->role) }}
                                        </flux:badge>
                                    @else
                                        <flux:select size="sm" wire:change="changeRole({{ $member->id }}, $event.target.value)">
                                            <option value="member" @selected($member->pivot->role === 'member')>{{ __('Member') }}</option>
                                            <option value="admin" @selected($member->pivot->role === 'admin')>{{ __('Admin') }}</option>
                                        </flux:select>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell align="end">
                                    @if ($member->pivot->role !== 'owner' && $member->id !== auth()->id())
                                        <flux:button variant="ghost" size="sm" icon="trash"
                                            wire:click="removeMember({{ $member->id }})"
                                            wire:confirm="Remove {{ $member->name }} from this business?" />
                                    @endif
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>

            @if (\App\Domains\Lien\Waivers\WaiverEntitlements::isSubscribed($this->business))
                <flux:separator />

                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <flux:heading size="sm">{{ __('Lien Waiver seats') }}</flux:heading>
                        <flux:text class="mt-1 text-sm text-zinc-500">
                            {{ \App\Domains\Lien\Waivers\WaiverEntitlements::assignedSeats($this->business) }}
                            of your team hold a paid seat. Assign, remove, or reassign seats and manage the subscription.
                        </flux:text>
                    </div>
                    <flux:button href="{{ route('lien.waivers.seats') }}" variant="ghost" icon="ticket" wire:navigate>
                        {{ __('Manage seats') }}
                    </flux:button>
                </div>
            @endif

            @if ($this->pendingInvitations()->isNotEmpty())
                <div class="space-y-3">
                    <flux:heading size="sm">{{ __('Pending invitations') }}</flux:heading>

                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>{{ __('Email') }}</flux:table.column>
                            <flux:table.column>{{ __('Expires') }}</flux:table.column>
                            <flux:table.column align="end"><span class="sr-only">{{ __('Actions') }}</span></flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach ($this->pendingInvitations() as $invitation)
                                <flux:table.row wire:key="invitation-{{ $invitation->id }}">
                                    <flux:table.cell>
                                        <div class="font-medium text-text-primary">{{ $invitation->email }}</div>
                                        <flux:badge size="sm" :color="$this->roleBadgeColor($invitation->role)">
                                            {{ ucfirst($invitation->role) }}
                                        </flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell class="text-zinc-500">
                                        {{ $invitation->expires_at->eastern()->format('M j, Y') }}
                                    </flux:table.cell>
                                    <flux:table.cell align="end">
                                        <div class="flex items-center justify-end gap-1">
                                            <flux:button variant="ghost" size="sm"
                                                wire:click="resendInvite({{ $invitation->id }})">
                                                {{ __('Resend') }}
                                            </flux:button>
                                            <flux:button variant="ghost" size="sm" icon="trash"
                                                wire:click="revokeInvite({{ $invitation->id }})"
                                                wire:confirm="Revoke this invitation?" />
                                        </div>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </div>
            @endif
        </div>
    </x-pages::settings.layout>
</section>
