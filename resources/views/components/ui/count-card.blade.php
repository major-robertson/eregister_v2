@props(['count', 'label'])
<div class="sticky top-4 rounded-lg border-2 border-action/30 bg-action/5 px-6 py-8 text-center">
    <div class="text-4xl font-bold text-action">{{ $count }}</div>
    <div class="mt-1 text-sm text-text-secondary">{{ $label }}</div>
</div>
