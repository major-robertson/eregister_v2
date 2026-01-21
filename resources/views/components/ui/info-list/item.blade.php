@props(['label'])
<div class="flex justify-between py-4 text-sm first:pt-0 last:pb-0">
    <dt class="text-text-secondary">{{ $label }}</dt>
    <dd class="font-medium text-text-primary text-right">{{ $slot ?: '-' }}</dd>
</div>
