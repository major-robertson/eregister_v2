<div class="w-full max-w-2xl text-center">
    <flux:icon name="check-circle" class="mx-auto size-12 text-green-500" />
    <flux:heading size="xl" class="mt-3">
        All set — your {{ $title }} {{ count($documents) > 1 ? 'are' : 'is' }} signed
    </flux:heading>
    <flux:text class="mt-2">
        A signed copy{{ count($documents) > 1 ? ' of each letter' : '' }} is available to download below,
        and our team has been notified.
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
</div>
