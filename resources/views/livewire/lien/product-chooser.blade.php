<section class="space-y-6" aria-labelledby="lien-chooser-heading">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 id="lien-chooser-heading" class="text-2xl font-bold text-zinc-900 dark:text-white">What do you need to happen?</h2>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Choose the state the job is in to see its deadlines and prices.</p>
        </div>
        <label class="block text-sm">
            <span class="font-medium text-zinc-700 dark:text-zinc-300">State</span>
            <select wire:model.live="state" class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 sm:w-56 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                <option value="">Choose a state</option>
                @foreach ($states as $code => $name)
                    <option value="{{ $code }}">{{ $name }}</option>
                @endforeach
            </select>
        </label>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($cards as $card)
            <div wire:key="lien-chooser-{{ $card['key'] }}" class="flex flex-col rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <h3 class="text-base font-semibold text-zinc-900 dark:text-white">{{ $card['need'] }}</h3>
                <p class="mt-0.5 text-xs font-semibold uppercase tracking-wide text-amber-700 dark:text-amber-400">{{ $card['name'] }}</p>
                <p class="mt-3 text-sm text-zinc-600 dark:text-zinc-400">{{ $card['does'] }}</p>
                <p class="mt-1 text-sm text-zinc-500">{{ $card['does_not'] }}</p>

                @if ($card['deadline'])
                    <p class="mt-3 text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ $card['deadline'] }}</p>
                @endif

                @foreach ($card['prerequisites'] as $prerequisite)
                    <p class="mt-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900 dark:border-amber-700 dark:bg-amber-900/20 dark:text-amber-200">
                        {{ $prerequisite['sentence'] }}
                        <a href="{{ $prerequisite['url'] }}" class="font-semibold underline">{{ $prerequisite['label'] }}</a>
                    </p>
                @endforeach

                @if ($card['not_served'])
                    <p class="mt-3 rounded-lg border border-zinc-200 bg-zinc-50 p-3 text-sm text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">{{ $card['not_served'] }}</p>
                @endif

                <div class="mt-auto pt-4">
                    @if ($card['prices'])
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">
                            Self-serve <span class="font-semibold text-zinc-900 dark:text-white">{{ $card['prices']['self_serve'] }}</span>
                            &middot; Full service <span class="font-semibold text-zinc-900 dark:text-white">{{ $card['prices']['full_service'] }}</span>
                        </p>
                    @endif
                    @if ($card['turnaround'])
                        <p class="mt-1 text-xs text-zinc-500">{{ $card['turnaround'] }}</p>
                    @endif

                    @if ($card['start_url'])
                        <a href="{{ $card['start_url'] }}" class="mt-3 inline-flex items-center gap-1 rounded-lg bg-zinc-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-zinc-700 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200">
                            Start
                            <span aria-hidden="true">&rarr;</span>
                            <span class="sr-only">a {{ strtolower($card['name']) }}</span>
                        </a>
                    @elseif ($card['talk_url'])
                        <a href="{{ $card['talk_url'] }}" class="mt-3 inline-flex items-center gap-1 rounded-lg border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-900 transition hover:bg-zinc-50 dark:border-zinc-600 dark:text-white dark:hover:bg-zinc-800">
                            Talk to us
                        </a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    @if ($compare)
        <div class="space-y-4 pt-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h3 class="text-xl font-bold text-zinc-900 dark:text-white">Self-serve and full service, side by side{{ $stateName ? ' in '.$stateName : '' }}</h3>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Both start with a document prepared for your project. The difference is who signs, sends and files it.</p>
                </div>
                <label class="block text-sm">
                    <span class="font-medium text-zinc-700 dark:text-zinc-300">Document</span>
                    <select wire:model.live="document" class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 sm:w-56 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                        @foreach ($orderable as $key => $name)
                            <option value="{{ $key }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </label>
            </div>

            @if ($comparison)
                <x-lien.service-comparison :rows="$comparison['rows']" :prices="$comparison['prices']" />
            @elseif ($comparisonNotServed)
                <p class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 text-sm text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">{{ $comparisonNotServed }}</p>
            @else
                <p class="rounded-lg border border-dashed border-zinc-300 p-4 text-sm text-zinc-600 dark:border-zinc-600 dark:text-zinc-400">Choose a state above to compare self-serve and full service there.</p>
            @endif
        </div>
    @endif
</section>
