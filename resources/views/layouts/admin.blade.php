@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-bg-light">
    <flux:sidebar sticky collapsible class="border-e border-border bg-slate-50/50">
        <flux:sidebar.header>
            <x-app-logo :sidebar="true" href="{{ route('admin.home') }}" badge="Admin" wire:navigate />
            <flux:sidebar.collapse class="lg:hidden" />
        </flux:sidebar.header>

        <flux:sidebar.nav>
            <flux:sidebar.group :heading="__('Applications')">
                <flux:sidebar.item icon="document-text" :href="route('admin.liens.board')"
                    :current="request()->routeIs('admin.liens.*')" wire:navigate>
                    {{ __('Liens') }}
                </flux:sidebar.item>
            </flux:sidebar.group>

            @role('admin')
            <flux:sidebar.group :heading="__('System')">
                <flux:sidebar.item icon="chart-bar" :href="route('admin.stats')"
                    :current="request()->routeIs('admin.stats')" wire:navigate>
                    {{ __('Stats') }}
                </flux:sidebar.item>
                <flux:sidebar.item icon="clipboard-document-list" :href="route('admin.lien-stats')"
                    :current="request()->routeIs('admin.lien-stats')" wire:navigate>
                    {{ __('Lien Stats') }}
                </flux:sidebar.item>
                <flux:sidebar.item icon="megaphone" :href="route('admin.marketing')"
                    :current="request()->routeIs('admin.marketing')" wire:navigate>
                    {{ __('Marketing') }}
                </flux:sidebar.item>
                <flux:sidebar.item icon="user-group" :href="route('admin.users.index')"
                    :current="request()->routeIs('admin.users.*')" wire:navigate>
                    {{ __('Users') }}
                </flux:sidebar.item>
                <flux:sidebar.item icon="building-office" :href="route('admin.businesses.index')"
                    :current="request()->routeIs('admin.businesses.*')" wire:navigate>
                    {{ __('Businesses') }}
                </flux:sidebar.item>
                <flux:sidebar.item icon="users" :href="route('admin.roles')"
                    :current="request()->routeIs('admin.roles')" wire:navigate>
                    {{ __('Roles') }}
                </flux:sidebar.item>
            </flux:sidebar.group>
            @endrole
        </flux:sidebar.nav>

        <flux:spacer />

        <flux:sidebar.nav>
            <flux:sidebar.item icon="arrow-left" :href="route('dashboard')" wire:navigate>
                {{ __('Exit Admin') }}
            </flux:sidebar.item>
        </flux:sidebar.nav>

        <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
    </flux:sidebar>

    <!-- Mobile Header -->
    <flux:header class="lg:hidden border-b border-border bg-slate-50/50">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <x-app-logo href="{{ route('admin.home') }}" badge="Admin" wire:navigate />

        <flux:spacer />

        <flux:dropdown position="top" align="end">
            <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" />

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
                        {{ __('Exit Admin') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                        class="w-full cursor-pointer">
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