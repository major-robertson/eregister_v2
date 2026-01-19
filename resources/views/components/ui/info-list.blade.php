@props(['items' => []])
<dl class="divide-y divide-border">
    @foreach($items as $label => $value)
        <div class="flex justify-between py-3 text-sm">
            <dt class="text-text-secondary">{{ $label }}</dt>
            <dd class="font-medium text-text-primary">{{ $value ?: '-' }}</dd>
        </div>
    @endforeach
    {{ $slot }}
</dl>
