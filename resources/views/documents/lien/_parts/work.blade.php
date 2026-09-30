{{-- The general description of the labor, services, equipment or materials furnished. --}}
@php $work = $doc['filing']['description_of_work']; @endphp
<div class="block">
    <div>@if ($work){!! nl2br(e($work)) !!}@else<span class="fill fill-wide">&nbsp;</span>@endif</div>
</div>
