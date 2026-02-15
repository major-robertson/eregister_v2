<div
    x-data="{
        selectedState: @entangle('selectedState'),
        scrollToState(state) {
            if (!state) return;
            const el = document.getElementById('state-' + state);
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    }"
    x-init="$nextTick(() => { if (selectedState) scrollToState(selectedState) })"
>
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.liens.board') }}" class="text-gray-500 hover:text-gray-700" wire:navigate>
                <flux:icon name="arrow-left" class="size-5" />
            </a>
            <flux:heading size="xl">Lien Rules Overview</flux:heading>
        </div>

        <div class="w-full sm:w-64">
            <flux:select
                x-model="selectedState"
                x-on:change="scrollToState(selectedState)"
                placeholder="Jump to state..."
            >
                <flux:select.option value="">All States</flux:select.option>
                @foreach ($stateList as $st)
                    <flux:select.option value="{{ $st }}">{{ $st }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>
    </div>

    {{-- State Cards --}}
    <div class="space-y-8">
        @foreach ($stateRules as $rule)
            @php
                $prelimRules = $rule->deadlineRules->filter(fn ($r) => $r->documentType?->slug === 'prelim_notice');
                $noiRules = $rule->deadlineRules->filter(fn ($r) => $r->documentType?->slug === 'noi');
                $lienRules = $rule->deadlineRules->filter(fn ($r) => $r->documentType?->slug === 'mechanics_lien');
                $resLienRules = $lienRules->filter(fn ($r) => $r->effective_scope === 'residential');
                $comLienRules = $lienRules->filter(fn ($r) => $r->effective_scope === 'commercial');
            @endphp
            <div
                id="state-{{ $rule->state }}"
                class="scroll-mt-24 rounded-lg border border-border bg-white shadow-sm"
                x-bind:class="selectedState === '{{ $rule->state }}' && 'ring-2 ring-blue-400'"
            >
                {{-- State Header --}}
                <div class="flex items-center justify-between border-b border-border bg-gray-50 px-6 py-4 rounded-t-lg">
                    <flux:heading size="lg">{{ $rule->state }}</flux:heading>
                    <div class="flex items-center gap-2">
                        @if ($rule->statute_url)
                            <a href="{{ $rule->statute_url }}" target="_blank" rel="noopener"
                               class="text-xs text-blue-600 hover:underline">
                                Statute
                            </a>
                        @endif
                    </div>
                </div>

                <div class="grid gap-6 p-6 lg:grid-cols-2">
                    {{-- Claimant Rights --}}
                    <div>
                        <flux:text class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">Claimant Rights</flux:text>
                        <div class="flex flex-wrap gap-2">
                            @php
                                $rights = [
                                    'GC' => $rule->gc_has_lien_rights,
                                    'Sub' => $rule->sub_has_lien_rights,
                                    'Sub-Sub' => $rule->subsub_has_lien_rights,
                                    'Supplier→Owner' => $rule->supplier_owner_has_lien_rights,
                                    'Supplier→GC' => $rule->supplier_gc_has_lien_rights,
                                    'Supplier→Sub' => $rule->supplier_sub_has_lien_rights,
                                ];
                            @endphp
                            @foreach ($rights as $label => $hasRights)
                                <flux:badge size="sm" color="{{ $hasRights ? 'green' : 'red' }}">
                                    {{ $label }}: {{ $hasRights ? 'Yes' : 'No' }}
                                </flux:badge>
                            @endforeach
                        </div>
                    </div>

                    {{-- Preliminary Notice --}}
                    <div>
                        <flux:text class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">Preliminary Notice</flux:text>
                        @if ($rule->pre_notice_required)
                            <flux:text class="text-sm">
                                <span class="font-medium">Required for:</span> {{ $rule->pre_notice_required_for }}
                            </flux:text>
                            <flux:text class="text-sm">
                                <span class="font-medium">Delivery:</span> {{ $rule->prelim_delivery_method }}
                                &middot; <span class="font-medium">Recipients:</span> {{ $rule->prelim_recipients }}
                            </flux:text>
                            @if ($prelimRules->isNotEmpty())
                                <div class="mt-1 space-y-0.5">
                                    @foreach ($prelimRules as $pr)
                                        <flux:text class="text-xs text-gray-600">
                                            {{ ucfirst(str_replace('_', ' ', $pr->claimant_type)) }}:
                                            {{ $this->formatDeadlineOffset($pr) }}
                                        </flux:text>
                                    @endforeach
                                </div>
                            @endif
                        @else
                            <flux:badge size="sm" color="zinc">Not required</flux:badge>
                        @endif
                    </div>

                    {{-- NOI --}}
                    <div>
                        <flux:text class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">Notice of Intent (NOI)</flux:text>
                        @if ($noiRules->isNotEmpty())
                            <div class="space-y-0.5">
                                @foreach ($noiRules as $nr)
                                    <flux:text class="text-sm">
                                        {{ ucfirst(str_replace('_', ' ', $nr->claimant_type)) }}:
                                        {{ $nr->offset_days }} days before lien filing
                                    </flux:text>
                                @endforeach
                            </div>
                        @elseif ($rule->noi_lead_time_days)
                            <flux:text class="text-sm">{{ $rule->noi_lead_time_days }} days lead time</flux:text>
                        @else
                            <flux:badge size="sm" color="zinc">Not required</flux:badge>
                        @endif
                    </div>

                    {{-- NOC Effects --}}
                    <div>
                        <flux:text class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">NOC Effects</flux:text>
                        <div class="space-y-0.5 text-sm">
                            <flux:text class="text-sm">
                                <span class="font-medium">Shortens deadline:</span> {{ $rule->noc_shortens_deadline ? 'Yes' : 'No' }}
                                @if ($rule->lien_after_noc_days)
                                    ({{ $rule->lien_after_noc_days }} days after NOC)
                                @endif
                            </flux:text>
                            @if ($rule->noc_requires_prior_prelim)
                                <flux:text class="text-sm text-amber-700">NOC requires prior prelim</flux:text>
                            @endif
                            @if ($rule->noc_eliminates_rights_if_no_prelim)
                                <flux:text class="text-sm text-red-700">NOC eliminates rights if no prelim sent</flux:text>
                            @endif
                        </div>
                    </div>

                    {{-- Mechanics Lien Deadlines --}}
                    <div class="lg:col-span-2">
                        <flux:text class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">Mechanics Lien Deadlines</flux:text>
                        @if ($lienRules->isNotEmpty())
                            <div class="grid gap-4 sm:grid-cols-2">
                                {{-- Residential --}}
                                <div>
                                    <flux:text class="mb-1 text-xs font-medium text-gray-700">Residential</flux:text>
                                    @if ($resLienRules->isNotEmpty())
                                        <div class="space-y-0.5">
                                            @foreach ($resLienRules as $lr)
                                                <flux:text class="text-xs text-gray-600">
                                                    {{ ucfirst(str_replace('_', ' ', $lr->claimant_type)) }}:
                                                    {{ $this->formatDeadlineOffset($lr) }}
                                                </flux:text>
                                            @endforeach
                                        </div>
                                    @else
                                        <flux:text class="text-xs text-gray-400">No rules</flux:text>
                                    @endif
                                </div>
                                {{-- Commercial --}}
                                <div>
                                    <flux:text class="mb-1 text-xs font-medium text-gray-700">Commercial</flux:text>
                                    @if ($comLienRules->isNotEmpty())
                                        <div class="space-y-0.5">
                                            @foreach ($comLienRules as $lr)
                                                <flux:text class="text-xs text-gray-600">
                                                    {{ ucfirst(str_replace('_', ' ', $lr->claimant_type)) }}:
                                                    {{ $this->formatDeadlineOffset($lr) }}
                                                </flux:text>
                                            @endforeach
                                        </div>
                                    @else
                                        <flux:text class="text-xs text-gray-400">No rules</flux:text>
                                    @endif
                                </div>
                            </div>
                        @else
                            <flux:text class="text-sm text-gray-400">No deadline rules configured</flux:text>
                        @endif
                    </div>

                    {{-- Post-Lien Notice --}}
                    <div>
                        <flux:text class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">Post-Lien Notice</flux:text>
                        @if ($rule->post_lien_notice_required)
                            <flux:text class="text-sm">
                                <span class="font-medium">Required:</span> within {{ $rule->post_lien_notice_days }} days
                            </flux:text>
                            @if ($rule->post_lien_notice_recipients)
                                <flux:text class="text-xs text-gray-600">Recipients: {{ $rule->post_lien_notice_recipients }}</flux:text>
                            @endif
                        @else
                            <flux:badge size="sm" color="zinc">Not required</flux:badge>
                        @endif
                    </div>

                    {{-- Enforcement --}}
                    <div>
                        <flux:text class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">Enforcement</flux:text>
                        <div class="space-y-0.5 text-sm">
                            @if ($rule->enforcement_deadline_months)
                                <flux:text class="text-sm">
                                    {{ rtrim(rtrim(number_format($rule->enforcement_deadline_months, 1), '0'), '.') }} months
                                    from {{ str_replace('_', ' ', $rule->enforcement_deadline_trigger) }}
                                </flux:text>
                            @elseif ($rule->enforcement_deadline_days)
                                <flux:text class="text-sm">
                                    {{ $rule->enforcement_deadline_days }} days
                                    from {{ str_replace('_', ' ', $rule->enforcement_deadline_trigger) }}
                                </flux:text>
                            @endif
                            <flux:text class="text-xs text-gray-500">Method: {{ str_replace('_', ' ', $rule->enforcement_calc_method) }}</flux:text>
                        </div>
                    </div>

                    {{-- Filing Requirements --}}
                    <div>
                        <flux:text class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">Filing Requirements</flux:text>
                        <div class="flex flex-wrap gap-2">
                            <flux:badge size="sm" color="{{ $rule->notarization_required ? 'amber' : 'zinc' }}">
                                Notarization: {{ $rule->notarization_required ? 'Required' : 'No' }}
                            </flux:badge>
                            <flux:badge size="sm" color="zinc">
                                Verification: {{ ucfirst($rule->verification_type) }}
                            </flux:badge>
                            <flux:badge size="sm" color="{{ $rule->efile_allowed ? 'green' : 'zinc' }}">
                                E-file: {{ $rule->efile_allowed ? 'Allowed' : 'No' }}
                            </flux:badge>
                        </div>
                        <flux:text class="mt-1 text-xs text-gray-600">
                            Filing location: {{ str_replace('_', ' ', $rule->filing_location) }}
                        </flux:text>
                    </div>

                    {{-- Special Rules --}}
                    <div>
                        <flux:text class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">Special Rules</flux:text>
                        <div class="space-y-0.5 text-sm">
                            @if ($rule->owner_occupied_special_rules)
                                <flux:text class="text-sm text-amber-700">
                                    Owner-occupied: {{ str_replace('_', ' ', $rule->owner_occupied_restriction_type) }}
                                </flux:text>
                            @else
                                <flux:text class="text-xs text-gray-500">No owner-occupied restrictions</flux:text>
                            @endif

                            @if (!$rule->tenant_project_lien_allowed)
                                <flux:text class="text-sm text-red-700">Tenant projects: lien NOT allowed</flux:text>
                            @elseif ($rule->tenant_project_restrictions && $rule->tenant_project_restrictions !== 'none')
                                <flux:text class="text-sm text-amber-700">
                                    Tenant projects: {{ str_replace('_', ' ', $rule->tenant_project_restrictions) }}
                                </flux:text>
                            @else
                                <flux:text class="text-xs text-gray-500">No tenant restrictions</flux:text>
                            @endif

                            @if ($rule->wrongful_lien_penalty && $rule->wrongful_lien_penalty !== 'none')
                                <flux:text class="text-sm text-red-700">
                                    Wrongful lien penalty: {{ $rule->wrongful_lien_penalty }}
                                </flux:text>
                                @if ($rule->penalty_details)
                                    <flux:text class="text-xs text-gray-600">{{ $rule->penalty_details }}</flux:text>
                                @endif
                            @endif
                        </div>
                    </div>

                    {{-- Notes & Statute References --}}
                    @if ($rule->notes || ($rule->statute_references && count($rule->statute_references) > 0))
                        <div class="lg:col-span-2 border-t border-gray-100 pt-3">
                            <flux:text class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">Notes & References</flux:text>
                            @if ($rule->notes)
                                <flux:text class="text-sm text-gray-700">{{ $rule->notes }}</flux:text>
                            @endif
                            @if ($rule->statute_references && count($rule->statute_references) > 0)
                                <flux:text class="mt-1 text-xs text-gray-500">
                                    Statutes: {{ implode(', ', $rule->statute_references) }}
                                </flux:text>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
