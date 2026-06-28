<div class="w-full max-w-2xl">
    <flux:heading size="xl">{{ $consent['heading'] }}</flux:heading>
    <flux:text class="mt-2">{{ $consent['agreement'] }}</flux:text>

    <div class="mt-6 space-y-3 rounded-lg border border-border bg-white p-6 text-sm text-gray-700">
        @foreach ($consent['disclosures'] as $key => $disclosure)
        <p wire:key="disclosure-{{ $key }}">
            <span class="font-medium capitalize">{{ str_replace('_', ' ', $key) }}.</span>
            {{ $disclosure }}
        </p>
        @endforeach
    </div>

    <form wire:submit="accept" class="mt-6 space-y-4">
        <flux:checkbox wire:model="acknowledged" :label="$consent['checkbox']" />
        <flux:error name="acknowledged" />

        <flux:button type="submit" variant="primary" icon="check">{{ $consent['accept_button'] }}</flux:button>
    </form>
</div>
