@extends('demo.mdcps.layout')

@section('body')
    <div class="flex min-h-screen items-center justify-center px-4 py-12">
        <div class="w-full max-w-sm">
            <div class="flex flex-col items-center text-center">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-[#0b3d6b] text-lg font-bold text-white">M</div>
                <h1 class="mt-4 text-xl font-bold text-slate-900">Miami-Dade County Public Schools</h1>
                <p class="mt-1 text-sm text-slate-500">Content Management System &mdash; sign in</p>
            </div>

            <form method="POST" action="{{ route('mdcps-demo.admin.login.attempt') }}"
                class="mt-8 grid gap-5 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                @csrf

                @error('username')
                    <div role="alert" class="flex items-start gap-2 rounded-lg border border-danger/30 bg-danger/10 px-4 py-3 text-sm font-medium text-danger">
                        <svg class="mt-0.5 h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        {{ $message }}
                    </div>
                @enderror

                <div class="grid gap-1.5">
                    <label for="username" class="text-sm font-medium text-slate-700">Username</label>
                    <input id="username" name="username" type="text" value="{{ old('username') }}" required autofocus
                        class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#0b5cab] focus:outline-none focus:ring-2 focus:ring-[#0b5cab]/20" />
                </div>

                <div class="grid gap-1.5">
                    <label for="password" class="text-sm font-medium text-slate-700">Password</label>
                    <input id="password" name="password" type="password" required
                        class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#0b5cab] focus:outline-none focus:ring-2 focus:ring-[#0b5cab]/20" />
                </div>

                <button type="submit" class="rounded-lg bg-[#0b3d6b] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#0b5cab]">
                    Sign in
                </button>
            </form>

            <div class="mt-4 rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 py-3 text-center text-xs text-slate-500">
                Sandbox credentials &mdash;
                <span class="font-semibold text-slate-700">{{ config('mdcps_demo.username') }}</span>
                /
                <span class="font-semibold text-slate-700">{{ config('mdcps_demo.password') }}</span>
            </div>

            <p class="mt-6 text-center text-sm">
                <a href="{{ route('mdcps-demo.home') }}" class="font-medium text-[#0b5cab] hover:underline">&larr; Back to the public site</a>
            </p>
        </div>
    </div>
@endsection
