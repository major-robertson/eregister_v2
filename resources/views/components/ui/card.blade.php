@props(['padding' => true])
<div {{ $attributes->class(['rounded-lg border border-border bg-white']) }}>
    @isset($header)
        <div class="border-b border-border px-6 py-4">
            {{ $header }}
        </div>
    @endisset
    <div @class(['px-6 py-5' => $padding, 'p-0' => !$padding])>
        {{ $slot }}
    </div>
</div>
