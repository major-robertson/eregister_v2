<div class="w-full max-w-2xl">
    @if (session('error'))
    <flux:callout variant="danger" icon="x-circle" class="mb-4">{{ session('error') }}</flux:callout>
    @endif

    <flux:heading size="xl">Review &amp; sign your {{ $title }}</flux:heading>
    <flux:text class="mt-2">{{ $intent }}</flux:text>

    <div class="mt-6 space-y-2">
        @foreach ($documents as $document)
        <div wire:key="review-{{ $document->id }}" class="flex items-center justify-between rounded-lg border border-border bg-white p-3">
            <div class="min-w-0">
                <flux:text class="font-mono text-xs text-gray-500">{{ $document->document_identifier }}</flux:text>
                <flux:text class="font-medium">{{ $loop->iteration }}. {{ $document->label }}</flux:text>
            </div>
            <flux:badge size="sm" color="zinc">Ready</flux:badge>
        </div>
        @endforeach
    </div>

    <form wire:submit="signAll" class="mt-6 space-y-4 rounded-lg border border-border bg-white p-6">
        <flux:field>
            <flux:label>{{ $typedNameLabel }}</flux:label>
            <flux:input wire:model.live.debounce.300ms="adoptedName" placeholder="Your full legal name" autocomplete="name" />
            <flux:error name="adoptedName" />
        </flux:field>

        @if (trim($adoptedName) !== '')
        <div class="rounded border border-gray-200 bg-gray-50 p-3">
            <div class="text-xs text-gray-500">Your adopted signature</div>
            <div style="font-family: Georgia, 'Times New Roman', serif; font-style: italic; font-size: 1.75rem; line-height: 1.4;">{{ $adoptedName }}</div>
        </div>
        @endif

        <flux:button type="submit" variant="primary" icon="pencil-square" class="w-full">{{ $signButton }}</flux:button>
        <flux:text class="text-center text-xs text-gray-500">
            By clicking “{{ $signButton }}”, you confirm you intend to be legally bound by your electronic
            signature on each listed letter.
        </flux:text>
    </form>
</div>
