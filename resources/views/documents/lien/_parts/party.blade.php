{{--
    One party as a mailing block: company, contact name, address lines and
    (with $contact) phone and email. A missing party or address prints ruled
    blanks. Vars: $party (array|null), $contact (bool, optional).
--}}
<div class="block">
    @if ($party !== null && ($party['display_name'] ?? null) !== null)
        @if ($party['company'])
            <div>{{ $party['company'] }}</div>
        @endif
        @if ($party['name'] && $party['name'] !== $party['company'])
            <div>{{ $party['name'] }}</div>
        @endif
        @forelse ($party['address_lines'] as $line)
            <div>{{ $line }}</div>
        @empty
            <div><span class="fill fill-wide">&nbsp;</span></div>
        @endforelse
        @if (! empty($contact))
            @if ($party['phone'])
                <div>Telephone: {{ $party['phone'] }}</div>
            @endif
            @if ($party['email'])
                <div>Email: {{ $party['email'] }}</div>
            @endif
        @endif
    @else
        <div><span class="fill fill-wide">&nbsp;</span></div>
        <div><span class="fill fill-wide">&nbsp;</span></div>
    @endif
</div>
