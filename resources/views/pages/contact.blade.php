@extends('layouts.landing')

@section('title', 'Contact Us - ' . config('app.name', 'eRegister'))

@section('content')
<div class="bg-white py-16 lg:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <h1 class="text-4xl font-bold tracking-tight text-zinc-900">Contact Us</h1>
            <p class="mt-4 text-lg text-zinc-600">
                Have questions? We're here to help. Send us a message and we'll respond as soon as possible.
            </p>
        </div>

        <div class="mx-auto mt-12 max-w-lg">
            <div class="rounded-2xl border border-zinc-200 bg-white p-8 shadow-sm">
                <livewire:contact-form />
            </div>
        </div>

    </div>
</div>
@endsection
