@props(['title', 'subtitle' => null])
<div class="mb-6 flex items-start justify-between">
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
