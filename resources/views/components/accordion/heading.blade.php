<button type="button" {{ $attributes->class('group/accordion-heading flex w-full cursor-pointer items-center justify-between text-start text-sm font-medium text-zinc-800') }}
    x-on:click="open = ! open" x-bind:aria-expanded="open ? 'true' : 'false'" aria-expanded="false">
    <span class="flex-1">{{ $slot }}</span>
    <svg class="ms-6 size-5 shrink-0 text-zinc-300 transition-transform group-hover/accordion-heading:text-zinc-800"
        x-bind:class="open ? 'rotate-180 text-zinc-800!' : ''" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
        fill="currentColor" aria-hidden="true">
        <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
    </svg>
</button>
