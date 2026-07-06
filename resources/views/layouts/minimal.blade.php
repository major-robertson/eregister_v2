<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased">
        <div class="flex min-h-screen flex-col">
            {{-- Simple header with logo --}}
            <header class="border-b border-border px-6 py-4">
                <a href="{{ route('home') }}" class="flex items-center" wire:navigate>
                    <img src="/img/logo/eregister-logo-dark-svg.svg" alt="eRegister" class="h-8" />
                </a>
            </header>

            {{-- Main content - centered --}}
            <main class="flex flex-1 items-center justify-center px-6 py-12">
                {{ $slot }}
            </main>
        </div>

        @persist('toast')
            <flux:toast />
        @endpersist
        @fluxScripts
        @stack('scripts')
    </body>
</html>
