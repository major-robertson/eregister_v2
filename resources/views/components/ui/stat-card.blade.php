@props(['value', 'label', 'icon', 'color' => 'primary'])
@php
$colorClasses = match($color) {
    'success' => 'bg-success/10 text-success',
    'warning' => 'bg-warning/30 text-yellow-700',
    'danger' => 'bg-danger/10 text-danger',
    default => 'bg-primary/10 text-primary',
};
@endphp
<div class="flex items-center justify-between rounded-lg border border-border bg-white px-6 py-5">
    <div>
        <div class="text-3xl font-bold text-text-primary">{{ $value }}</div>
        <div class="mt-1 text-sm text-text-secondary">{{ $label }}</div>
    </div>
    <div class="flex h-12 w-12 items-center justify-center rounded-xl {{ $colorClasses }}">
        <flux:icon :name="$icon" class="size-6" />
    </div>
</div>
