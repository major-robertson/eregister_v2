@props(['type' => 'success', 'message', 'dismissable' => true])
@php
$classes = match($type) {
    'success' => 'bg-action text-white',
    'error' => 'bg-danger text-white',
    'warning' => 'bg-warning text-yellow-900',
    default => 'bg-primary text-white',
};
$iconName = match($type) {
    'success' => 'check-circle',
    'error' => 'x-circle',
    'warning' => 'exclamation-triangle',
    default => 'information-circle',
};
@endphp
<div {{ $attributes->class(['flex items-center gap-3 rounded-lg px-4 py-3 shadow-lg', $classes]) }}>
    <flux:icon :name="$iconName" class="size-5 shrink-0" />
    <div class="flex-1">
        <div class="font-medium">{{ ucfirst($type) }}</div>
        <div class="text-sm opacity-90">{{ $message }}</div>
    </div>
    @if($dismissable)
        <button type="button" class="opacity-70 hover:opacity-100">
            <flux:icon name="x-mark" class="size-4" />
        </button>
    @endif
</div>
