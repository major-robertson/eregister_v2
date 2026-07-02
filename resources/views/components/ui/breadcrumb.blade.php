@props(['items' => []])
<nav {{ $attributes->class('text-sm') }}>
    <ol class="flex items-center gap-2">
        @foreach($items as $item)
            @if(!$loop->last && isset($item['url']))
                <li>
                    <a href="{{ $item['url'] }}" wire:navigate class="font-medium text-text-secondary transition-colors hover:text-text-primary">{{ $item['label'] }}</a>
                </li>
            @else
                <li class="font-medium text-text-secondary">{{ $item['label'] }}</li>
            @endif
            @unless($loop->last)
                <li aria-hidden="true">
                    <flux:icon name="chevron-right" class="size-3 text-zinc-400" />
                </li>
            @endunless
        @endforeach
    </ol>
</nav>
