@props([
    'exports',          // code => DeadlineRulesExport::forState() array
    'state' => null,    // a state code fixes the state (state pages) and hides the select
])
{{-- Free lien deadline calculator. Alpine evaluates the rule JSON inlined
     below (resources/js/lien-deadline-calculator.js); no network calls. --}}
@php
    $state = $state ? strtoupper($state) : null;
    $source = 'lien-deadline-rules';
    $roles = [
        'gc' => 'General contractor (hired by the owner)',
        'subcontractor' => 'Subcontractor (hired by the general contractor)',
        'sub_sub_contractor' => 'Sub-subcontractor (hired by a subcontractor)',
        'supplier_to_owner' => 'Material supplier to the owner',
        'supplier_to_contractor' => 'Material supplier to the general contractor',
        'supplier_to_subcontractor' => 'Material supplier to a subcontractor',
    ];
    $field = 'mt-1.5 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200';
@endphp
<div {{ $attributes->merge(['class' => 'rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm sm:p-8']) }}
    x-data="lienDeadlineCalculator({ source: '{{ $source }}', state: '{{ $state }}' })" data-lien-deadline-calculator>
    <script type="application/json" id="{{ $source }}">{!! json_encode($exports, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>

    <form class="grid gap-5 sm:grid-cols-2" @submit.prevent>
        @unless ($state)
        <div>
            <label for="ldc-state" class="block text-sm font-medium text-zinc-900">State where the project is</label>
            <select id="ldc-state" x-model="state" class="{{ $field }}">
                <option value="">Choose a state</option>
                @foreach ($exports as $code => $export)
                <option value="{{ $code }}">{{ $export['name'] }}</option>
                @endforeach
            </select>
        </div>
        @endunless

        <div>
            <label for="ldc-role" class="block text-sm font-medium text-zinc-900">Your role on the project</label>
            <select id="ldc-role" x-model="role" class="{{ $field }}">
                <option value="">Choose your role</option>
                @foreach ($roles as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <fieldset @class(['sm:col-span-2' => ! $state])>
            <legend class="block text-sm font-medium text-zinc-900">Project type</legend>
            <div class="mt-2.5 flex flex-wrap gap-x-6 gap-y-2 text-sm text-zinc-700">
                <label class="inline-flex items-center gap-2">
                    <input type="radio" name="ldc-scope" value="residential" x-model="scope" class="h-4 w-4 accent-amber-600">
                    Residential
                </label>
                <label class="inline-flex items-center gap-2">
                    <input type="radio" name="ldc-scope" value="commercial" x-model="scope" class="h-4 w-4 accent-amber-600">
                    Commercial
                </label>
            </div>
        </fieldset>

        <template x-for="input in inputs" :key="input.key">
            <div>
                <label :for="'ldc-' + input.key" class="block text-sm font-medium text-zinc-900">
                    <span x-text="input.label"></span>
                    <span x-show="input.optional" class="font-normal text-zinc-500">(optional)</span>
                </label>
                <input type="date" :id="'ldc-' + input.key" x-model="dates[input.key]" class="{{ $field }}">
                <p x-show="input.help" x-text="input.help" class="mt-1 text-xs text-zinc-500"></p>
            </div>
        </template>

        <template x-if="asksNoc && dates.noc && current.noc_requires_prior_prelim">
            <label class="flex items-start gap-2 text-sm text-zinc-700 sm:col-span-2">
                <input type="checkbox" x-model="prelimBeforeNoc" class="mt-0.5 h-4 w-4 accent-amber-600">
                I sent my preliminary notice before the notice of completion was filed
            </label>
        </template>
    </form>

    <p x-show="!ready" class="mt-6 text-sm text-zinc-500">
        Choose {{ $state ? 'your role' : 'a state and your role' }} to see which dates you need.
    </p>

    <template x-if="ready">
        <div class="mt-8" aria-live="polite">
            <p class="font-semibold text-zinc-900">Your <span x-text="current.name"></span> deadlines</p>
            <div class="mt-3 overflow-x-auto rounded-xl border border-zinc-200">
                <table class="min-w-full divide-y divide-zinc-200 text-left text-sm">
                    <thead class="bg-zinc-50 text-xs uppercase tracking-wider text-zinc-500">
                        <tr>
                            <th scope="col" class="px-4 py-3 font-semibold">Document</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Who</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Deadline</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        <template x-for="row in rows" :key="row.key">
                            <tr>
                                <td class="px-4 py-4 align-top font-medium text-zinc-900" x-text="row.label"></td>
                                <td class="px-4 py-4 align-top text-zinc-600" x-text="row.who"></td>
                                <td class="px-4 py-4 align-top">
                                    <span :class="row.muted ? 'text-zinc-500' : (row.text ? 'text-zinc-900' : 'font-semibold text-zinc-900')" x-text="row.value"></span>
                                    <span x-show="row.passed" class="ml-2 inline-flex rounded-full bg-red-50 px-2 py-0.5 text-xs font-semibold text-red-700">Passed</span>
                                    <p x-show="row.how" x-text="row.how" class="mt-1 text-xs text-zinc-500"></p>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <p x-show="current.attorney_referral" class="mt-4 text-sm text-zinc-700">
                <span x-text="current.name"></span> liens are filed through the courts by an attorney.
                <a href="{{ route('register') }}" class="font-medium text-zinc-900 underline">Get matched with a lien attorney</a>.
            </p>
            <p x-show="!current.attorney_referral" class="mt-4 text-sm text-zinc-700">
                We can prepare and record the lien for you before the deadline.
                <a href="{{ route('register') }}" class="font-medium text-zinc-900 underline">Start your lien</a>.
            </p>
        </div>
    </template>

    <p class="mt-6 border-t border-zinc-100 pt-4 text-xs text-zinc-500">
        These dates come from the same rules our lien filing service uses. They are not moved for weekends or holidays.
        Deadlines turn on facts specific to your project. Confirm with counsel before relying on them.
    </p>
</div>
