<div class="space-y-6">
    <x-ui.page-header title="Lien Projects">
        <x-slot:actions>
            <flux:button href="{{ route('lien.projects.create') }}" variant="primary" icon="plus">
                New Project
            </flux:button>
        </x-slot:actions>
    </x-ui.page-header>

    @if(session('message'))
        <flux:callout color="green" icon="check-circle">
            {{ session('message') }}
        </flux:callout>
    @endif

    <x-ui.card>
        <div class="mb-4">
            <flux:input
                wire:model.live.debounce.300ms="search"
                placeholder="Search projects..."
                icon="magnifying-glass"
            />
        </div>

        @if($projects->isEmpty())
            <div class="text-center py-12">
                <flux:icon name="folder" class="mx-auto h-12 w-12 text-zinc-400" />
                <h3 class="mt-2 text-sm font-semibold text-zinc-900 dark:text-white">No projects</h3>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    Get started by creating a new lien project.
                </p>
                <div class="mt-6">
                    <flux:button href="{{ route('lien.projects.create') }}" variant="primary" icon="plus">
                        New Project
                    </flux:button>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider cursor-pointer"
                                wire:click="sortBy('name')">
                                Project Name
                                @if($sortField === 'name')
                                    <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="inline h-4 w-4" />
                                @endif
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">
                                Location
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">
                                Next Deadline
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider cursor-pointer"
                                wire:click="sortBy('created_at')">
                                Created
                                @if($sortField === 'created_at')
                                    <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}" class="inline h-4 w-4" />
                                @endif
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach($projects as $project)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div>
                                        <a href="{{ route('lien.projects.show', $project) }}"
                                           class="font-medium text-zinc-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400">
                                            {{ $project->name }}
                                        </a>
                                        @if($project->job_number)
                                            <span class="ml-2 text-xs text-zinc-500">{{ $project->job_number }}</span>
                                        @endif
                                    </div>
                                    <div class="text-sm text-zinc-500">
                                        {{ $project->claimant_type->label() }}
                                    </div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $project->jobsite_city }}, {{ $project->jobsite_state }}
                                    @if($project->jobsite_county)
                                        <br><span class="text-xs">{{ $project->jobsite_county }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    @php
                                        $nextDeadline = $project->deadlines->first();
                                    @endphp
                                    @if($nextDeadline)
                                        @php
                                            $daysRemaining = $nextDeadline->daysRemaining();
                                        @endphp
                                        <div class="text-sm">
                                            {{ $nextDeadline->documentType->name }}
                                        </div>
                                        @if($daysRemaining !== null && $daysRemaining < 0)
                                            <flux:badge color="red">Overdue</flux:badge>
                                        @elseif($daysRemaining !== null && $daysRemaining <= 7)
                                            <flux:badge color="amber">{{ $daysRemaining }} days</flux:badge>
                                        @elseif($nextDeadline->due_date)
                                            <span class="text-xs text-zinc-500">{{ $nextDeadline->due_date->format('M j, Y') }}</span>
                                        @endif
                                    @else
                                        <span class="text-zinc-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $project->created_at->format('M j, Y') }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-right text-sm">
                                    <flux:button href="{{ route('lien.projects.show', $project) }}" variant="ghost" size="sm">
                                        View
                                    </flux:button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $projects->links() }}
            </div>
        @endif
    </x-ui.card>
</div>
