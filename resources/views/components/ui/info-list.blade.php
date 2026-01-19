@props(['items' => []])
<dl class="divide-y divide-border">
    @foreach($items as $label => $value)
        <div class="flex justify-between py-4 text-sm first:pt-0 last:pb-0">
            <dt class="text-text-secondary">{{ $label }}</dt>
            <dd class="font-medium text-text-primary text-right">{{ $value ?: '-' }}</dd>
        </div>
    @endforeach
    {{ $slot }}
</dl>
