@props(['key', 'title' => null])

@php
    /** @var \App\Support\Workspaces\Workspace $workspace */
    $workspace = app(\App\Support\Workspaces\WorkspaceRegistry::class)->find($key);

    if (! $workspace) {
        throw new \RuntimeException("Unknown workspace key: {$key}");
    }

    // Tailwind v4 only emits classes it sees as static literals in scanned
    // files, and opacity-modified classes pulled from a config string
    // aren't reliably detected. Keep the per-color tint classes as literals
    // here so the JIT scanner picks them up.
    $sidebarTints = [
        'amber' => 'bg-amber-50/30',
        'emerald' => 'bg-emerald-50/30',
        'blue' => 'bg-blue-50/30',
        'zinc' => 'bg-zinc-50/30',
    ];
    $sidebarTint = $sidebarTints[$workspace->badgeColor] ?? 'bg-zinc-50';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-bg-light">
        <flux:sidebar sticky collapsible class="border-e border-border {{ $sidebarTint }} max-lg:bg-zinc-50">
            <flux:sidebar.header>
                <x-app-logo
                    :sidebar="true"
                    :href="route($workspace->dashboardRoute)"
                    :badge="$workspace->badge"
                    :badge-color="$workspace->badgeColor"
                    wire:navigate
                />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__($workspace->navHeading)">
                    @foreach ($workspace->nav as $item)
                        <flux:sidebar.item
                            :icon="$item['icon']"
                            :href="route($item['route'])"
                            :current="request()->routeIs($item['current_pattern'])"
                            wire:navigate
                        >
                            {{ __($item['label']) }}
                        </flux:sidebar.item>
                    @endforeach
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="cog-6-tooth" :href="route('profile.edit')" :current="request()->routeIs('profile.*')" wire:navigate>
                    {{ __('Settings') }}
                </flux:sidebar.item>
                <flux:sidebar.item icon="arrow-left" :href="route('dashboard')" wire:navigate>
                    {{ __('Exit to Dashboard') }}
                </flux:sidebar.item>
            </flux:sidebar.nav>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden border-b border-border {{ $sidebarTint }}">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <x-app-logo
                :href="route($workspace->dashboardRoute)"
                :badge="$workspace->badge"
                :badge-color="$workspace->badgeColor"
                wire:navigate
            />

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
                        <flux:menu.item :href="route('dashboard')" icon="home" wire:navigate>
                            {{ __('Exit to Dashboard') }}
                        </flux:menu.item>
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

        <x-ui.toast-container />

        @fluxScripts
        @stack('scripts')
    </body>
</html>
