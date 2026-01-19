@props(['title' => 'Tips'])
<div class="rounded-lg border border-border bg-white p-5">
    <h4 class="mb-4 font-semibold text-text-primary">{{ $title }}</h4>
    <ul class="space-y-3">
        {{ $slot }}
    </ul>
</div>
