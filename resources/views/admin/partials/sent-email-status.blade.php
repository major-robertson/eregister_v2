{{-- Delivery status and View link for one sent email (EREG-154). Needs $email; $withViewLink defaults to true. --}}
<div class="mt-1 flex flex-wrap items-center gap-2">
    <flux:badge size="sm" color="{{ $email->statusColor() }}" icon="envelope">{{ $email->statusLabel() }}</flux:badge>
    @if ($email->status_detail)
        <flux:text class="text-xs text-gray-500">{{ $email->status_detail }}</flux:text>
    @endif
    @if ($withViewLink ?? true)
        <flux:button size="xs" variant="ghost" icon="eye" wire:click="viewEmail({{ $email->id }})">View email</flux:button>
    @endif
</div>
