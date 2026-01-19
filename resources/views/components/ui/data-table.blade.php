@props(['headers' => []])
<div class="overflow-x-auto">
    <table class="w-full text-left text-sm">
        @if(count($headers))
            <thead class="border-b border-border bg-zinc-50 text-xs uppercase tracking-wider text-text-secondary">
                <tr>
                    @foreach($headers as $header)
                        <th class="px-6 py-3 font-medium">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
        @endif
        <tbody class="divide-y divide-border">
            {{ $slot }}
        </tbody>
    </table>
</div>
