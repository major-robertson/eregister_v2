{{-- Alpine stand-in for <flux:accordion>, for marketing pages that run the
     Alpine-only marketing.js bundle (no Flux JS). Same markup classes as Flux:
     <x-accordion> / <x-accordion.item> / <x-accordion.heading> / <x-accordion.content>. --}}
<div {{ $attributes->class('block') }}>
    {{ $slot }}
</div>
