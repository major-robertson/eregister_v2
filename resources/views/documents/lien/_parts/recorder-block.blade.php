{{--
    Page-1 space reserved for the recorder's stamp. Its height is the state's
    first-page top margin minus the page margin (3in − 1in by default), so
    the rule sits exactly where the recorder expects it. The preparer /
    return-to block prints in the left half of that space, as Washington
    (RCW 65.04.045) and Kansas (K.S.A. 28-115) allow, unless the state or
    county wants the space completely clear (recording.preparer_in_space
    false), in which case it prints just below the rule. Missouri's
    grantor/grantee index block (RSMo 59.310) follows when asked for.
--}}
@php
    $rec = $doc['form']['recording'];
    $inSpace = $rec['preparer_in_space'] ?? true;
    $space = (float) ($doc['form']['recorder_space_in'] ?? 2.0);
@endphp
<div class="recorder-space" style="height: {{ $space }}in;">
    @if ($inSpace)
        @include('documents.lien._parts.preparer')
    @endif
</div>
<div class="recorder-rule">Space above this line for recorder's use only</div>
@if (! $inSpace)
    <div class="preparer-below">
        @include('documents.lien._parts.preparer')
    </div>
@endif
@if (! empty($rec['index_block']))
    @include('documents.lien._parts.index-block')
@endif
