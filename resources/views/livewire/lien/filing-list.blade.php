<div class="space-y-6">
    <x-ui.page-header title="Filings" />

    @if(session('message'))
        <flux:callout color="green" icon="check-circle">
            {{ session('message') }}
        </flux:callout>
    @endif

    <x-ui.card>
        <div class="mb-4 flex flex-col gap-4 sm:flex-row sm:items-center">
            <div class="flex-1">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search filings..."
                    icon="magnifying-glass"
                />
            </div>
            <div class="w-full sm:w-48">
                <flux:select wire:model.live="statusFilter">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                    @endforeach
                </flux:select>
            </div>
        </div>

        @if($filings->isEmpty())
            <div class="text-center py-12">
                <flux:icon name="document-text" class="mx-auto h-12 w-12 text-zinc-400" />
                <h3 class="mt-2 text-sm font-semibold text-zinc-900 dark:text-white">No filings</h3>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    Start a filing from a project's detail page.
                </p>
                <div class="mt-6">
                    <flux:button href="{{ route('lien.projects.index') }}" variant="primary" icon="folder">
                        View Projects
                    </flux:button>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">
                                Document Type
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">
                                Project
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">
                                Jurisdiction
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">
                                Created
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach($filings as $filing)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <flux:badge :color="$filing->status->color()">
                                        {{ $filing->status->label() }}
                                    </flux:badge>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-white">
                                    {{ $filing->documentType?->name ?? '-' }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <a href="{{ route('lien.projects.show', $filing->project) }}"
                                       class="text-sm font-medium text-zinc-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400"
                                       wire:navigate>
                                        {{ $filing->project?->name ?? '-' }}
                                    </a>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                    @if($filing->jurisdiction_county && $filing->jurisdiction_state)
                                        {{ $filing->jurisdiction_county }}, {{ $filing->jurisdiction_state }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $filing->created_at->format('M j, Y') }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-right text-sm">
                                    @if($filing->status === \App\Domains\Lien\Enums\FilingStatus::Draft)
                                        <flux:button href="{{ route('lien.filings.show', $filing) }}" variant="primary" size="sm" wire:navigate>
                                            Resume
                                        </flux:button>
                                    @elseif($filing->status === \App\Domains\Lien\Enums\FilingStatus::AwaitingPayment)
                                        <flux:button href="{{ route('lien.filings.checkout', $filing) }}" variant="primary" size="sm" wire:navigate>
                                            Pay
                                        </flux:button>
                                    @else
                                        <flux:button href="{{ route('lien.filings.show', $filing) }}" variant="ghost" size="sm" wire:navigate>
                                            View
                                        </flux:button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $filings->links() }}
            </div>
        @endif
    </x-ui.card>
</div>
