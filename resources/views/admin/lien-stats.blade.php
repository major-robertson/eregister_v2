<div class="space-y-6">
    <div>
        <flux:heading size="xl">Lien Stats</flux:heading>
        <flux:text class="mt-1">View lien project progress and filing status across all users.</flux:text>
    </div>

    <div class="rounded-lg border border-border bg-white">
        <div class="flex items-center justify-between border-b border-border px-4 py-3">
            <flux:heading size="sm">All Lien Projects</flux:heading>
            <div class="w-80">
                <flux:input type="search" placeholder="Search by project, business, or user..."
                    wire:model.live.debounce.300ms="search" icon="magnifying-glass" />
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 text-left text-sm text-gray-500">
                    <tr>
                        <th class="px-4 py-3 font-medium">User</th>
                        <th class="px-4 py-3 font-medium">Business</th>
                        <th class="px-4 py-3 font-medium">Project</th>
                        <th class="px-4 py-3 font-medium">Progress</th>
                        <th class="px-4 py-3 font-medium">Wizard</th>
                        <th class="px-4 py-3 font-medium">Filings</th>
                        <th class="px-4 py-3 font-medium">Created</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($projects as $project)
                    @php
                    $progress = $this->getWizardProgress($project);
                    $filings = $this->getFilingSummary($project);
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            @if ($project->createdBy)
                            <div class="flex items-center gap-3">
                                <flux:avatar :initials="$project->createdBy->initials()" size="sm" />
                                <div>
                                    <flux:text class="font-medium">{{ $project->createdBy->name }}</flux:text>
                                    <flux:text class="text-sm text-gray-500">{{ $project->createdBy->email }}
                                    </flux:text>
                                </div>
                            </div>
                            @else
                            <flux:text class="text-gray-400">Unknown</flux:text>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if ($project->business)
                            <flux:text class="font-medium">{{ $project->business->name }}</flux:text>
                            @else
                            <flux:text class="text-gray-400">No business</flux:text>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <flux:text class="font-medium">{{ $project->name ?: 'Unnamed Project' }}</flux:text>
                            @if ($project->job_number)
                            <flux:text class="text-sm text-gray-500">#{{ $project->job_number }}</flux:text>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <flux:dropdown>
                                <flux:button variant="ghost" size="sm">
                                    {{ $progress['filled'] }}/{{ $progress['total'] }} fields
                                </flux:button>

                                <flux:menu class="w-72">
                                    <div class="px-3 py-2 space-y-3">
                                        @foreach ($progress['steps'] as $stepName => $stepData)
                                        <div>
                                            <div class="flex items-center justify-between mb-1">
                                                <flux:text class="font-medium text-sm">{{ $stepName }}</flux:text>
                                                <flux:badge size="sm"
                                                    :color="$stepData['filled'] === $stepData['total'] ? 'green' : 'zinc'">
                                                    {{ $stepData['filled'] }}/{{ $stepData['total'] }}
                                                </flux:badge>
                                            </div>
                                            <div class="space-y-1">
                                                @foreach ($stepData['fields'] as $fieldLabel => $isFilled)
                                                <div class="flex items-center gap-2 text-sm">
                                                    @if ($isFilled)
                                                    <flux:icon name="check-circle" class="size-4 text-green-500" />
                                                    @else
                                                    <flux:icon name="x-circle" class="size-4 text-gray-300" />
                                                    @endif
                                                    <span class="{{ $isFilled ? 'text-gray-700' : 'text-gray-400' }}">
                                                        {{ $fieldLabel }}
                                                    </span>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </flux:menu>
                            </flux:dropdown>
                        </td>
                        <td class="px-4 py-3">
                            @if ($project->isWizardComplete())
                            <flux:badge size="sm" color="green">Complete</flux:badge>
                            @else
                            <flux:badge size="sm" color="amber">In Progress</flux:badge>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if ($filings['total'] > 0)
                            <flux:tooltip
                                content="Total: {{ $filings['total'] }}, Paid: {{ $filings['paid'] }}, Complete: {{ $filings['complete'] }}, Draft: {{ $filings['draft'] }}">
                                <div class="flex items-center gap-2">
                                    <flux:badge size="sm" color="zinc">{{ $filings['total'] }}</flux:badge>
                                    @if ($filings['paid'] > 0)
                                    <flux:badge size="sm" color="green">{{ $filings['paid'] }} paid</flux:badge>
                                    @endif
                                </div>
                            </flux:tooltip>
                            @else
                            <flux:text class="text-gray-400">None</flux:text>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <flux:text class="text-sm text-gray-500">
                                {{ $project->created_at->format('M j, Y') }}
                            </flux:text>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center">
                            <flux:text class="text-gray-400">No lien projects found.</flux:text>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($projects->hasPages())
        <div class="border-t border-border px-4 py-3">
            {{ $projects->links() }}
        </div>
        @endif
    </div>
</div>