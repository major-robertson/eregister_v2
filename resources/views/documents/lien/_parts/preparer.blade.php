{{-- "Prepared by and return to" block, from config('lien.documents.preparer'). --}}
@php $preparer = $doc['preparer']; @endphp
<div class="preparer">
    <div><strong>Prepared by, recording requested by and return to:</strong></div>
    <div>{{ $preparer['name'] }}</div>
    @if ($preparer['attention'])
        <div>Attn: {{ $preparer['attention'] }}</div>
    @endif
    @forelse ($preparer['address_lines'] as $line)
        <div>{{ $line }}</div>
    @empty
        <div><span class="fill fill-wide">&nbsp;</span></div>
    @endforelse
    @if ($preparer['phone'])
        <div>{{ $preparer['phone'] }}</div>
    @endif
</div>
