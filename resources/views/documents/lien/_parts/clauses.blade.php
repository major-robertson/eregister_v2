{{--
    Statutory clauses from the state file. $only names the clause slot:
    'notice_box' is a single boxed notice (a paragraph string or a view name
    when it has structure); the list slots (after_property, before_signature,
    after_execution) hold paragraph strings or view names. Statutory text
    lives in the data files and clause views only; never edit it here.
--}}
@php
    $clauses = $doc['form']['clauses'];
    $slot = $only ?? null;
    $isView = fn ($value) => is_string($value) && str_starts_with($value, 'documents.lien.');
@endphp
@if ($slot === 'notice_box')
    @php $box = $clauses['notice_box'] ?? null; @endphp
    @if ($isView($box))
        @include($box)
    @elseif (is_string($box) && $box !== '')
        <div class="notice-box">{{ $box }}</div>
    @endif
@elseif ($slot !== null)
    @foreach ((array) ($clauses[$slot] ?? []) as $clause)
        @if ($isView($clause))
            @include($clause)
        @elseif (is_string($clause) && $clause !== '')
            <p>{{ $clause }}</p>
        @endif
    @endforeach
@endif
