<div class="w-full max-w-lg">
    {{-- Progress dots: 4 for continuous flow (all complete), 2 for standalone (all complete) --}}
    @php $totalUnifiedSteps = $this->isContinuousFlow ? 4 : 2; @endphp
    <div class="mb-16 flex justify-center gap-2">
        @for ($i = 1; $i <= $totalUnifiedSteps; $i++) <div class="h-2 w-2 rounded-full bg-primary transition-colors">
    </div>
    @endfor
</div>

<div class="text-center">
    <h1 class="text-3xl font-bold tracking-tight text-text-primary sm:text-4xl">
        Great, your profile has now been set up.
    </h1>
    <p class="mt-4 text-lg text-text-secondary">
        Let's add the project you need to track or file a lien on.
    </p>
</div>

<div class="mt-12 flex justify-center">
    <flux:button wire:click="proceed" variant="primary" icon-trailing="arrow-right">
        Next
    </flux:button>
</div>
</div>