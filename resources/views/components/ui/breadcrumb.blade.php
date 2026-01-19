@props(['items' => []])
<nav class="mb-2 text-sm">
    <ol class="flex items-center gap-2">
        @foreach($items as $item)
            @if(!$loop->last)
                <li>
                    <a href="{{ $item['url'] }}" class="text-primary hover:underline">{{ $item['label'] }}</a>
                </li>
                <li class="text-text-secondary">/</li>
            @else
                <li class="text-text-secondary">{{ $item['label'] }}</li>
            @endif
        @endforeach
    </ol>
</nav>
