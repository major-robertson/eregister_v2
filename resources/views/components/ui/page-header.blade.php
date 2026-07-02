@props([
    'title',
    'subtitle' => null,
    // Section identity: auto-detects the current workspace so every
    // workspace page gets its chip without per-page wiring. Pass
    // :chip="false" to suppress, or :workspace="$ws" to force one.
    'chip' => true,
    'workspace' => null,
    // Accepts an items array (rendered via x-ui.breadcrumb) or a
    // <x-slot:breadcrumbs> slot with custom markup.
    'breadcrumbs' => [],
])

@php
    if ($chip && ! $workspace) {
        $workspace = app(\App\Support\Workspaces\WorkspaceRegistry::class)->current();
    }

    $hasBreadcrumbs = $breadcrumbs instanceof \Illuminate\View\ComponentSlot
        ? $breadcrumbs->isNotEmpty()
        : count($breadcrumbs) > 0;
@endphp

<div class="mb-6">
    @if (($chip && $workspace) || $hasBreadcrumbs)
        <div class="mb-3 flex flex-wrap items-center gap-2">
            @if ($chip && $workspace)
                <x-ui.section-chip :workspace="$workspace" />
                @if ($hasBreadcrumbs)
                    <flux:icon name="chevron-right" class="size-3 text-zinc-400" aria-hidden="true" />
                @endif
            @endif

            @if ($hasBreadcrumbs)
                @if ($breadcrumbs instanceof \Illuminate\View\ComponentSlot)
                    {{ $breadcrumbs }}
                @else
                    <x-ui.breadcrumb :items="$breadcrumbs" />
                @endif
            @endif
        </div>
    @endif

    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-text-primary">{{ $title }}</h1>
            @if($subtitle)
                <p class="mt-1 text-text-secondary">{{ $subtitle }}</p>
            @endif
        </div>
        @isset($actions)
            <div class="flex items-center gap-3">
                {{ $actions }}
            </div>
        @endisset
    </div>
</div>
