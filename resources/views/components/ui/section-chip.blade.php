@props(['workspace'])

{{-- bg-*-500/10 and text-*-600 are safelisted via @source inline in app.css,
     so building the class names from the config color is safe here. --}}
<span {{ $attributes->class("inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold bg-{$workspace->badgeColor}-500/10 text-{$workspace->badgeColor}-600") }}>
    <flux:icon :icon="$workspace->icon" class="size-3.5 shrink-0" />
    {{ __($workspace->name) }}
</span>
