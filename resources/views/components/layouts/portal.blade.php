@props(['title' => null])

@php
    $registry = app(\App\Support\Workspaces\WorkspaceRegistry::class);

    $workspaces = collect($registry->all())->filter(fn ($workspace) => $workspace->enabled);

    /** @var \App\Support\Workspaces\Workspace|null $currentWorkspace */
    $currentWorkspace = $registry->current();

    // Tailwind v4 only emits classes it sees as static literals in scanned
    // files, and the color name comes from config/workspaces.php at runtime.
    // Keep the per-color identity classes as literals here so the JIT
    // scanner picks them up.
    $sectionDots = [
        'amber' => 'bg-amber-500',
        'indigo' => 'bg-indigo-500',
        'emerald' => 'bg-emerald-500',
        'blue' => 'bg-blue-500',
        'zinc' => 'bg-zinc-500',
    ];
    $sectionIcons = [
        'amber' => 'text-amber-600',
        'indigo' => 'text-indigo-600',
        'emerald' => 'text-emerald-600',
        'blue' => 'text-blue-600',
        'zinc' => 'text-zinc-600',
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-bg-light">
        <flux:sidebar sticky collapsible class="border-e border-border bg-zinc-50">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <livewire:business.business-dropdown />

            <flux:sidebar.nav>
                <flux:sidebar.item
                    icon="home"
                    :href="route('dashboard')"
                    :current="request()->routeIs('dashboard')"
                    :accent="false"
                    wire:navigate
                >
                    {{ __('Home') }}
                </flux:sidebar.item>

                @foreach ($workspaces as $workspace)
                    @php
                        $isCurrentWorkspace = $currentWorkspace?->key === $workspace->key;
                        $dot = $sectionDots[$workspace->badgeColor] ?? 'bg-zinc-500';
                    @endphp

                    {{-- Every workspace is a collapsible group (even
                         single-page ones, for consistency), open only for
                         the workspace you're currently in. --}}
                    <flux:sidebar.group
                        expandable
                        :expanded="$isCurrentWorkspace"
                        :heading="__($workspace->name)"
                    >
                        <x-slot:icon>
                            <span class="mx-1 block size-2 shrink-0 rounded-full {{ $dot }}"></span>
                        </x-slot:icon>

                        @foreach ($workspace->nav as $item)
                            @php $current = request()->routeIs($item['current_pattern']); @endphp
                            <flux:sidebar.item
                                :href="route($item['route'])"
                                :current="$current"
                                :accent="false"
                                class="data-current:[&_[data-content]]:font-semibold"
                                wire:navigate
                            >
                                <x-slot:icon>
                                    <flux:icon
                                        :icon="$item['icon']"
                                        class="size-4 shrink-0 {{ $current ? ($sectionIcons[$workspace->badgeColor] ?? '') : '' }} [[data-flux-sidebar-item]:hover_&]:text-current!"
                                    />
                                </x-slot:icon>
                                {{ __($item['label']) }}
                            </flux:sidebar.item>
                        @endforeach
                    </flux:sidebar.group>
                @endforeach
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="cog-6-tooth" :href="route('profile.edit')" :current="request()->routeIs('profile.*')" :accent="false" wire:navigate>
                    {{ __('Settings') }}
                </flux:sidebar.item>
            </flux:sidebar.nav>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile header -->
        <flux:header class="border-b border-border bg-zinc-50 lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            @if ($currentWorkspace)
                <x-ui.section-chip :workspace="$currentWorkspace" />
            @else
                <x-app-logo href="{{ route('dashboard') }}" wire:navigate />
            @endif

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        <flux:main>
            {{ $slot }}
        </flux:main>

        @persist('toast')
            <flux:toast />
        @endpersist

        @fluxScripts
        @stack('scripts')
    </body>
</html>
