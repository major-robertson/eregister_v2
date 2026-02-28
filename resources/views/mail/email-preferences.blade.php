<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Preferences — eRegister</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>
<body class="flex min-h-screen items-center justify-center bg-zinc-50 antialiased">
    <div class="mx-auto w-full max-w-lg px-6 py-12">
        <a href="{{ route('home') }}">
            <img src="/img/logo/eregister-logo-dark-svg.svg" alt="eRegister" class="mx-auto mb-8 h-8 brightness-0" />
        </a>
        <h1 class="text-center text-xl font-semibold text-zinc-900">Email Preferences</h1>
        <p class="mt-2 text-center text-sm text-zinc-500">
            Choose which emails you'd like to receive from eRegister.
        </p>
        <div class="mt-8">
            <livewire:email-preferences :user="$user" />
        </div>
        <p class="mt-8 text-center text-xs text-zinc-400">
            <a href="{{ route('home') }}" class="hover:text-zinc-600">← Back to eRegister</a>
        </p>
    </div>
    @fluxScripts
</body>
</html>
