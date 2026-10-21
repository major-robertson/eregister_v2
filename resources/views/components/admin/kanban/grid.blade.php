@props([
    'compact' => true,
    'xlColumns' => 6,
])

{{--
    Generic kanban grid wrapper. Toggles between responsive grid and
    horizontal-scroll layouts. Children should be <x-admin.kanban.column>
    components.

    Props:
    - $compact: bool. true = responsive grid (4-6 columns); false =
                horizontal scroll for many columns (used when searching
                where 8+ status columns are shown).
    - $xlColumns: 5 or 6, the columns per row on wide screens (the lien
                board's 10 columns sit in two rows of 5).
--}}

@php
    $xlClass = $xlColumns === 5 ? 'xl:grid-cols-5' : 'xl:grid-cols-6';
@endphp

<div class="{{ $compact ? 'grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 '.$xlClass : 'flex gap-4 overflow-x-auto pb-4' }}">
    {{ $slot }}
</div>
