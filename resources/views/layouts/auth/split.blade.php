<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased">
        <div class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
            <div class="relative hidden h-full flex-col bg-zinc-900 p-10 text-white lg:flex">
                <div class="absolute inset-0 bg-zinc-900"></div>
                <a href="{{ route('home') }}" class="relative z-20 flex items-center" wire:navigate>
                    <img src="/img/logo/eregister-logo-light-svg.svg" alt="eRegister" class="h-9 brightness-0 invert" />
                </a>

                @php
                    [$message, $author] = str(Illuminate\Foundation\Inspiring::quotes()->random())->explode('-');
                @endphp

                <div class="relative z-20 mt-auto">
                    <blockquote class="space-y-2">
                        <flux:heading size="lg" class="text-white">&ldquo;{{ trim($message) }}&rdquo;</flux:heading>
                        <footer><flux:heading class="text-zinc-400">{{ trim($author) }}</flux:heading></footer>
                    </blockquote>
                </div>
            </div>
            <div class="w-full lg:p-8">
                <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]">
                    <a href="{{ route('home') }}" class="z-20 flex flex-col items-center gap-2 font-medium lg:hidden" wire:navigate>
                        <span class="flex h-14 w-14 items-center justify-center rounded-md">
                            <x-app-logo-icon variant="dark" class="size-14" />
                        </span>

                        <span class="sr-only">{{ config('app.name', 'eRegister') }}</span>
                    </a>
                    {{ $slot }}
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
