<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Lien Filings Board</flux:heading>
            <flux:text class="mt-1">Manage and process lien filings across all businesses.</flux:text>
        </div>
        <flux:button href="{{ route('admin.liens.board-all') }}" variant="subtle" icon="view-columns" wire:navigate>
            View All Statuses
        </flux:button>
    </div>

    <flux:input type="search" placeholder="Search by name, email, address, business..."
        wire:model.live.debounce.300ms="search" icon="magnifying-glass" />

    @if ($search)
    <div class="flex items-center gap-3">
        <flux:badge size="sm" color="zinc">
            {{ $resultCount }} {{ Str::plural('result', $resultCount) }}
        </flux:badge>
        <flux:text class="text-sm text-gray-500">
            for "{{ $search }}"
        </flux:text>
    </div>
    @endif

    @if (strlen($search) >= 2)
    <div class="flex items-center gap-2">
        <flux:button size="sm" wire:click="searchBusinesses"
            variant="{{ $searchMode?->value === 'businesses' ? 'primary' : 'subtle' }}"
            icon="building-office">
            Search Businesses
        </flux:button>
        <flux:button size="sm" wire:click="searchLiens"
            variant="{{ $searchMode?->value === 'liens' ? 'primary' : 'subtle' }}"
            icon="document-text">
            Search Liens
        </flux:button>
        @if ($searchMode)
        <flux:button size="sm" wire:click="clearSearchMode" variant="ghost" icon="arrow-left">
            Back to Board
        </flux:button>
        @endif
    </div>
    @endif

    <div wire:loading.delay class="flex items-center gap-2 text-sm text-gray-500">
        <flux:icon name="arrow-path" class="size-4 animate-spin" />
        Loading...
    </div>

    @if ($searchMode?->value === 'businesses')
        {{-- Search Businesses Results --}}
        <div wire:loading.remove>
            @if ($businessResults->isEmpty())
            <div class="flex h-48 items-center justify-center rounded-lg border border-dashed border-gray-300">
                <div class="text-center">
                    <flux:icon name="building-office" class="mx-auto size-8 text-gray-400" />
                    <flux:text class="mt-2 text-gray-500">No businesses found for "{{ $search }}"</flux:text>
                </div>
            </div>
            @else
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($businessResults as $business)
                <a href="{{ route('admin.businesses.show', $business) }}"
                    class="block rounded-lg border border-border bg-white p-4 shadow-sm transition hover:border-blue-300 hover:shadow-md"
                    wire:navigate
                    wire:key="business-{{ $business->id }}">
                    <div class="flex items-start justify-between gap-2">
                        <flux:heading size="sm" class="truncate">{{ $business->name }}</flux:heading>
                    </div>

                    @if ($business->businessAddressLine())
                    <flux:text class="mt-1 text-xs text-gray-500 truncate">
                        {{ $business->businessAddressLine() }}
                    </flux:text>
                    @endif

                    {{-- Associated Users (capped at 3 + overflow) --}}
                    @if ($business->users->isNotEmpty())
                    <div class="mt-3 space-y-1">
                        @foreach ($business->users->take(3) as $user)
                        <div class="flex items-center gap-2">
                            <flux:icon name="user" class="size-3 shrink-0 text-gray-400" />
                            <flux:text class="text-xs text-gray-600 truncate">
                                {{ $user->name }} &middot; {{ $user->email }}
                            </flux:text>
                        </div>
                        @endforeach
                        @if ($business->users->count() > 3)
                        <flux:text class="text-xs text-gray-400">
                            +{{ $business->users->count() - 3 }} more
                        </flux:text>
                        @endif
                    </div>
                    @endif

                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <flux:badge size="sm" color="zinc">
                            {{ $business->lien_projects_count }} {{ Str::plural('lien project', $business->lien_projects_count) }}
                        </flux:badge>
                        <flux:badge size="sm" color="zinc">
                            {{ $business->form_applications_count }} {{ Str::plural('application', $business->form_applications_count) }}
                        </flux:badge>
                    </div>
                </a>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $businessResults->links() }}
            </div>
            @endif
        </div>
    @elseif ($searchMode?->value === 'liens')
        {{-- Search Liens Results --}}
        <div wire:loading.remove>
            @if ($lienResults->isEmpty())
            <div class="flex h-48 items-center justify-center rounded-lg border border-dashed border-gray-300">
                <div class="text-center">
                    <flux:icon name="document-text" class="mx-auto size-8 text-gray-400" />
                    <flux:text class="mt-2 text-gray-500">No lien filings found for "{{ $search }}"</flux:text>
                </div>
            </div>
            @else
            <div class="overflow-hidden rounded-lg border border-border">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Business</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Filed By</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Document</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Service</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Created</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach ($lienResults as $filing)
                        <tr wire:key="filing-{{ $filing->id }}" class="transition hover:bg-gray-50">
                            <td class="whitespace-nowrap px-4 py-3">
                                <a href="{{ route('admin.liens.show', $filing->public_id) }}"
                                    class="text-sm font-medium text-blue-600 hover:text-blue-800 hover:underline"
                                    wire:navigate>
                                    {{ $filing->project?->business?->name ?? 'Unknown' }}
                                </a>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                @if ($filing->createdBy)
                                <flux:text class="text-sm">{{ $filing->createdBy->name }}</flux:text>
                                <flux:text class="text-xs text-gray-500">{{ $filing->createdBy->email }}</flux:text>
                                @else
                                <flux:text class="text-sm text-gray-400">Unknown</flux:text>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <flux:text class="text-sm">{{ $filing->documentType?->name ?? 'Unknown' }}</flux:text>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <flux:badge size="sm" color="{{ $filing->status->color() }}">
                                    {{ $filing->status->label() }}
                                </flux:badge>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <flux:badge size="sm"
                                    color="{{ $filing->service_level === \App\Domains\Lien\Enums\ServiceLevel::FullService ? 'indigo' : 'zinc' }}">
                                    {{ $filing->service_level?->label() ?? 'Unknown' }}
                                </flux:badge>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <flux:text class="text-sm text-gray-500">{{ $filing->created_at->format('M j, Y') }}</flux:text>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $lienResults->links() }}
            </div>
            @endif
        </div>
    @else
        {{-- Kanban Board (default) / All-status board (when searching) --}}
        @php $isSearching = strlen($search) > 0; @endphp
        <div class="{{ $isSearching ? 'flex gap-4 overflow-x-auto pb-4' : 'grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6' }}">
            @foreach ($columns as $column)
            @php
            $columnFilings = $filings->get($column->value) ?? collect();
            $count = $columnFilings->count();
            @endphp

            <div class="{{ $isSearching ? 'flex w-72 shrink-0 flex-col' : 'flex flex-col' }} rounded-lg border border-border bg-white">
                <!-- Column Header -->
                <div class="flex items-center justify-between border-b border-border px-4 py-3">
                    <div class="flex items-center gap-2">
                        <flux:badge color="{{ $column->color() }}" size="sm">
                            {{ $count }}
                        </flux:badge>
                        <flux:heading size="sm">{{ $column->label() }}</flux:heading>
                    </div>
                </div>

                <!-- Column Content -->
                <div class="flex-1 space-y-3 overflow-y-auto p-3" style="max-height: 70vh;">
                    @forelse ($columnFilings as $filing)
                    @php
                    $latestComment = $filing->events->first();
                    @endphp
                    <a href="{{ route('admin.liens.show', $filing->public_id) }}"
                        class="block rounded-lg border border-border bg-white p-3 shadow-sm transition hover:border-blue-300 hover:shadow-md"
                        wire:navigate>
                        <div class="flex items-start justify-between gap-2">
                            <flux:text class="font-medium text-gray-900 truncate">
                                {{ $filing->project?->business?->name ?? 'Unknown Business' }}
                            </flux:text>
                            <div class="flex shrink-0 items-center gap-1">
                                @if ($filing->project?->jobsite_state)
                                <flux:badge size="sm" color="zinc">{{ $filing->project->jobsite_state }}</flux:badge>
                                @endif
                                @if ($filing->needs_review)
                                <flux:badge color="amber" size="sm">Review</flux:badge>
                                @endif
                            </div>
                        </div>

                        @if ($filing->createdBy)
                        <flux:text class="mt-1 text-xs text-gray-500 truncate">
                            {{ $filing->createdBy->name }} &middot; {{ $filing->createdBy->email }}
                        </flux:text>
                        @endif

                        @if ($filing->project?->jobsite_address1)
                        <flux:text class="mt-1 text-xs text-gray-500 truncate">
                            {{ $filing->project->jobsiteAddressLine() }}
                        </flux:text>
                        @endif

                        <div class="mt-2 flex flex-wrap items-center gap-1">
                            <flux:badge size="sm" color="zinc">
                                {{ $filing->documentType?->name ?? 'Unknown' }}
                            </flux:badge>
                            <flux:badge size="sm"
                                color="{{ $filing->service_level === \App\Domains\Lien\Enums\ServiceLevel::FullService ? 'indigo' : 'zinc' }}">
                                {{ $filing->service_level?->label() ?? 'Unknown' }}
                            </flux:badge>
                            @if (! $isSearching)
                            <flux:badge size="sm" color="{{ $filing->status->color() }}">
                                {{ $filing->status->label() }}
                            </flux:badge>
                            @endif
                        </div>

                        @if ($latestComment)
                        <div class="mt-2 flex items-start gap-1.5">
                            <flux:icon name="chat-bubble-left" class="mt-0.5 size-3 shrink-0 text-gray-400" />
                            <flux:text class="text-xs text-gray-500 line-clamp-2">
                                {{ Str::limit($latestComment->payload_json['comment'] ?? '', 200) }}
                            </flux:text>
                        </div>
                        @endif

                        <flux:text class="mt-2 text-xs text-gray-400">
                            @if ($filing->paid_at)
                                Paid {{ $filing->paid_at->diffForHumans() }}
                            @else
                                {{ $filing->created_at->diffForHumans() }}
                            @endif
                        </flux:text>
                    </a>
                    @empty
                    <div class="flex h-24 items-center justify-center text-center">
                        <flux:text class="text-gray-400">No filings</flux:text>
                    </div>
                    @endforelse
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>
