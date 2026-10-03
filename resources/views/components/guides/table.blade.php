@props([])
{{-- A data table inside a guide body. The wrapper scrolls sideways on narrow
     screens; the article component styles th and td. Pass an id so tests and
     anchors can find the table. --}}
<div class="mt-6 overflow-x-auto rounded-2xl border border-zinc-200">
    <table {{ $attributes->merge(['class' => 'min-w-full divide-y divide-zinc-200 text-left text-sm']) }}>
        {{ $slot }}
    </table>
</div>
