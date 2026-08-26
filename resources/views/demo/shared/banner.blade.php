{{--
    eRegister banner shown at the top of every sales demo sandbox.

    Usage:
        @include('demo.shared.banner', ['demoLabel' => 'Clay County RFP 78-26'])

    Optional:
        'demoNote'    — text after the label (defaults to the standard prototype note)
        'demoExitUrl' — where "Exit demo" goes (defaults to the government landing page)
--}}
<div class="bg-slate-900 text-slate-100">
    <div class="mx-auto flex max-w-[1440px] flex-col items-start justify-between gap-1 px-4 py-2 text-xs sm:flex-row sm:items-center md:px-8 xl:px-12">
        <div class="flex items-center gap-2">
            <span class="inline-block h-1.5 w-1.5 flex-none rounded-full bg-amber-400"></span>
            <span>Concept demo for <strong class="font-semibold text-white">{{ $demoLabel }}</strong> by eRegister — {{ $demoNote ?? 'front-end prototype, sample data only' }}</span>
        </div>
        <a href="{{ $demoExitUrl ?? route('government.home') }}" class="inline-flex items-center gap-1 font-medium text-slate-300 transition hover:text-white">
            Exit demo
            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
        </a>
    </div>
</div>
