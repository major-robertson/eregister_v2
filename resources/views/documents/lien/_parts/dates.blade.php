{{--
    Furnishing dates and the state-specific date lines: completion date
    (Arizona) and the months of work for which payment is requested (Texas,
    from Document details).
--}}
@php
    $sections = $doc['form']['sections'];
    $dates = $doc['project']['dates'];
    $details = $doc['details'];
@endphp
<div class="block">
    @if ($sections['first_furnish'] ?? true)
        <div><span class="lbl">First furnished:</span> @if ($dates['first_furnish']){{ $dates['first_furnish'] }}@else<span class="fill">&nbsp;</span>@endif</div>
    @endif
    @if ($sections['last_furnish'] ?? true)
        <div><span class="lbl">Last furnished:</span> @if ($dates['last_furnish']){{ $dates['last_furnish'] }}@else<span class="fill">&nbsp;</span>@endif</div>
    @endif
    @if (! empty($sections['completion_date']))
        <div><span class="lbl">Date of completion of the work of improvement:</span> @if ($dates['completion']){{ $dates['completion'] }}@else<span class="fill">&nbsp;</span>@endif</div>
    @endif
    @if (! empty($sections['months_of_work']))
        <div><span class="lbl">Months in which the work was done and materials furnished for which payment is requested:</span> @if ($details['months_of_work']){{ $details['months_of_work'] }}@else<span class="fill fill-wide">&nbsp;</span>@endif</div>
    @endif
</div>
