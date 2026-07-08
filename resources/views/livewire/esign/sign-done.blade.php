<div class="w-full max-w-2xl text-center">
    <flux:icon name="check-circle" class="mx-auto size-12 text-green-500" />
    <flux:heading size="xl" class="mt-3">
        All set. Your {{ $title }} {{ count($documents) > 1 ? 'are' : 'is' }} signed.
    </flux:heading>
    <flux:text class="mt-2">
        @if ($isGuest)
            A signed copy{{ count($documents) > 1 ? ' of each document' : '' }} is available to download below.
            Both parties receive the signed copy by email.
        @else
            A signed copy{{ count($documents) > 1 ? ' of each letter' : '' }} is available to download below,
            and our team has been notified.
        @endif
    </flux:text>

    <div class="mt-6 space-y-2 text-left">
        @foreach ($documents as $document)
        <div wire:key="done-{{ $document->id }}" class="flex items-center justify-between rounded-lg border border-border bg-white p-3">
            <div class="min-w-0">
                <flux:text class="font-mono text-xs text-gray-500">{{ $document->document_identifier }}</flux:text>
                <flux:text class="font-medium">{{ $document->label }}</flux:text>
            </div>
            @if ($document->signed_at)
            <flux:button size="sm" icon="arrow-down-tray"
                :href="route('esign.sign.download', ['request' => $request->public_id, 'document' => $document->public_id])">
                Download
            </flux:button>
            @endif
        </div>
        @endforeach
    </div>

    @if ($isGuest)
        <div class="mt-8 rounded-lg border border-blue-200 bg-blue-50 p-5 text-left">
            <flux:heading size="lg">Keep track of everything you sign</flux:heading>
            <flux:text class="mt-1">
                Create a free account with this email and every waiver you've signed, including this one,
                will be waiting in your dashboard. Track lien deadlines and generate your own waivers for free, too.
            </flux:text>
            <flux:button variant="primary" icon="user-plus" :href="$registerUrl" class="mt-4">
                Create my free account
            </flux:button>
        </div>
    @endif
</div>
