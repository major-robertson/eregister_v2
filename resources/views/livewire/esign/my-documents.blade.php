<div class="w-full max-w-3xl">
    <flux:heading size="xl">Documents I've signed</flux:heading>
    <flux:text class="mt-2">
        Every document you've electronically signed with this email address, with signed copies to download.
    </flux:text>

    <div class="mt-6 space-y-3">
        @forelse ($this->requests as $request)
            <div wire:key="req-{{ $request->id }}" class="rounded-lg border border-border bg-white p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:text class="font-medium">
                            {{ config("esign.document_types.{$request->document_signing_policy_key}.title", 'Documents') }}
                        </flux:text>
                        <flux:text class="text-sm text-gray-500">
                            Signed {{ $request->completed_at?->eastern()->format('M j, Y g:i A') }} ET
                        </flux:text>
                    </div>
                    <flux:badge color="green">Signed</flux:badge>
                </div>
                <div class="mt-3 space-y-2">
                    @foreach ($request->documents as $document)
                        <div wire:key="doc-{{ $document->id }}" class="flex items-center justify-between rounded border border-border p-2">
                            <flux:text class="min-w-0 truncate text-sm">{{ $document->label }}</flux:text>
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
        @empty
            <div class="rounded-lg border border-dashed border-border p-8 text-center">
                <flux:text>Nothing signed yet. Documents you sign electronically will appear here.</flux:text>
            </div>
        @endforelse
    </div>
</div>
