<x-layouts::auth title="Join {{ $invitation->business->name }}">
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('Join :business', ['business' => $invitation->business->name])"
            :description="__(':inviter invited you to join as :role.', [
                'inviter' => $invitation->inviter?->name ?: __('A teammate'),
                'role' => $invitation->role === 'admin' ? __('an admin') : __('a member'),
            ])"
        />

        @if ($emailMatches)
            <form method="POST" action="{{ route('invitations.accept.store', $invitation) }}" class="flex flex-col gap-6">
                @csrf
                <flux:button type="submit" variant="primary" class="w-full" data-test="accept-invitation-button">
                    {{ __('Accept invitation') }}
                </flux:button>
            </form>

            <flux:text class="text-center text-sm text-zinc-500">
                {{ __('Accepting as :email', ['email' => auth()->user()->email]) }}
            </flux:text>
        @else
            <flux:callout color="amber" icon="exclamation-triangle">
                {{ __("You're signed in as :current, but this invitation was sent to :invited. Sign out, then open the link from your email again.", [
                    'current' => auth()->user()->email,
                    'invited' => $invitation->email,
                ]) }}
            </flux:callout>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <flux:button type="submit" variant="outline" class="w-full">
                    {{ __('Sign out') }}
                </flux:button>
            </form>
        @endif
    </div>
</x-layouts::auth>
