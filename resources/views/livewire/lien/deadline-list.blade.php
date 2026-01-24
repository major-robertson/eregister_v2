<div class="space-y-6">
    <x-ui.page-header title="Deadlines" />

    <x-ui.card>
        <div class="mb-4 flex flex-col gap-4 sm:flex-row sm:items-center">
            <div class="flex-1">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search deadlines..."
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

        @if($deadlines->isEmpty())
            <div class="text-center py-12">
                <flux:icon name="calendar" class="mx-auto h-12 w-12 text-zinc-400" />
                <h3 class="mt-2 text-sm font-semibold text-zinc-900 dark:text-white">No deadlines</h3>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    Deadlines will appear here once you create projects.
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
                                Due Date
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">
                                Days Remaining
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach($deadlines as $deadline)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <flux:badge :color="$deadline->status->color()">
                                        {{ $deadline->status->label() }}
                                    </flux:badge>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-white">
                                    {{ $deadline->documentType?->name ?? '-' }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <a href="{{ route('lien.projects.show', $deadline->project) }}"
                                       class="text-sm font-medium text-zinc-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400"
                                       wire:navigate>
                                        {{ $deadline->project?->name ?? '-' }}
                                    </a>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $deadline->due_date?->format('M j, Y') ?? '-' }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm">
                                    @php
                                        $days = $deadline->daysRemaining();
                                    @endphp
                                    @if($days === null)
                                        <span class="text-zinc-400">-</span>
                                    @elseif($deadline->isOverdue())
                                        <span class="text-red-600 dark:text-red-400 font-medium">
                                            {{ abs($days) }} day{{ abs($days) !== 1 ? 's' : '' }} overdue
                                        </span>
                                    @elseif($deadline->isDueSoon())
                                        <span class="text-amber-600 dark:text-amber-400 font-medium">
                                            {{ $days }} day{{ $days !== 1 ? 's' : '' }}
                                        </span>
                                    @else
                                        <span class="text-zinc-500 dark:text-zinc-400">
                                            {{ $days }} day{{ $days !== 1 ? 's' : '' }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-right text-sm">
                                    <flux:button href="{{ route('lien.projects.show', $deadline->project) }}" variant="ghost" size="sm" wire:navigate>
                                        View Project
                                    </flux:button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $deadlines->links() }}
            </div>
        @endif
    </x-ui.card>
</div>
