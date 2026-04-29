@props([
    'compact' => true,
])

{{--
    Generic kanban grid wrapper. Toggles between responsive grid and
    horizontal-scroll layouts. Children should be <x-admin.kanban.column>
    components.

    Props:
    - $compact: bool. true = responsive grid (4-6 columns); false =
                horizontal scroll for many columns (used when searching
                where 8+ status columns are shown).
--}}

<div class="{{ $compact ? 'grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6' : 'flex gap-4 overflow-x-auto pb-4' }}">
    {{ $slot }}
</div>
