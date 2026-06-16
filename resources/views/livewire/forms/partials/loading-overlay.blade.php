{{-- Loading overlay shown while removeRepeaterItem is in flight.
     Sits inside an absolute-positioned wrapper, so the parent
     container must declare `relative`. --}}
<div
    wire:loading.flex
    wire:target="removeRepeaterItem"
    class="absolute inset-0 z-10 items-center justify-center bg-white/75 dark:bg-zinc-800/75"
>
    <div class="flex items-center gap-2 text-text-secondary">
        <svg class="h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span>Updating...</span>
    </div>
</div>
