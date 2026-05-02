@php
    /**
     * Render a list of grouped fields (used by both step-level `groups`
     * and repeater-level `schema_groups`). Each group renders as its own
     * section. A group's `fields` entry can be either:
     *   - a string field key (full-width)
     *   - an array of string keys ['first_name', 'last_name']
     *     (rendered side-by-side in a 2-col grid)
     *
     * Variables in scope:
     *   - $groups        (array)  list of {title, fields} groups to render
     *   - $visibleFields (array)  map of fieldKey => fieldDef. Only keys
     *                             present here are rendered; keys absent
     *                             from this map are silently skipped (so
     *                             conditional `when` clauses just work).
     *   - $fieldPartial  (string) Blade partial path to render each field
     *                             ('livewire.forms.partials.field' for
     *                             step-level, '...repeater-subfield' for
     *                             repeater-level).
     *   - $fieldContext  (array)  base context dict merged into each
     *                             field include (prefix, data, etc.).
     *
     * Optional:
     *   - $sectionWrapper (string|null) 'card' wraps each non-empty group
     *                                   in <x-ui.card>; 'separator' uses
     *                                   <flux:separator>+heading (repeater-
     *                                   modal style); null = no wrapper.
     *                                   Defaults to 'card'.
     *   - $beforeFields  (string|null)  optional partial path inserted at
     *                                   the very top of each group's body
     *                                   (e.g. loading overlay for step
     *                                   groups). Receives $fieldContext.
     */
    $sectionWrapper = $sectionWrapper ?? 'card';
    $beforeFields = $beforeFields ?? null;

    // Resolve which keys in each group are actually visible. Empty
    // groups (every field hidden) are dropped entirely so we don't
    // render a card with just a heading and nothing inside.
    $resolvedGroups = [];
    foreach ($groups as $groupIndex => $group) {
        $resolvedEntries = [];
        foreach ($group['fields'] ?? [] as $entry) {
            if (is_array($entry)) {
                // Inline-row syntax. Collapse to single-column when only
                // one of the row's fields is currently visible.
                $rowKeys = array_values(array_filter(
                    $entry,
                    fn ($k) => array_key_exists($k, $visibleFields)
                ));
                if (count($rowKeys) === 0) {
                    continue;
                }
                if (count($rowKeys) === 1) {
                    $resolvedEntries[] = $rowKeys[0];
                } else {
                    $resolvedEntries[] = $rowKeys;
                }
            } elseif (array_key_exists($entry, $visibleFields)) {
                $resolvedEntries[] = $entry;
            }
        }

        if (! empty($resolvedEntries)) {
            $resolvedGroups[] = [
                'index' => $groupIndex,
                'title' => $group['title'] ?? null,
                'entries' => $resolvedEntries,
            ];
        }
    }
@endphp

@foreach ($resolvedGroups as $resolved)
    @if ($sectionWrapper === 'card')
        <x-ui.card class="relative">
            @if ($beforeFields)
                @include($beforeFields, $fieldContext)
            @endif
            @if ($resolved['title'])
                <flux:heading size="lg" class="mb-4">{{ $resolved['title'] }}</flux:heading>
            @endif
            <div class="space-y-6">
                @foreach ($resolved['entries'] as $entry)
                    @include('livewire.forms.partials.grouped-fields-row', [
                        'entry' => $entry,
                        'visibleFields' => $visibleFields,
                        'fieldPartial' => $fieldPartial,
                        'fieldContext' => $fieldContext,
                    ])
                @endforeach
            </div>
        </x-ui.card>
    @elseif ($sectionWrapper === 'separator')
        @if ($resolved['index'] > 0 || ! empty($resolved['title']))
            <flux:separator />
        @endif
        @if ($resolved['title'])
            <flux:heading size="sm" class="text-zinc-600 dark:text-zinc-400">
                {{ $resolved['title'] }}
            </flux:heading>
        @endif
        @foreach ($resolved['entries'] as $entry)
            @include('livewire.forms.partials.grouped-fields-row', [
                'entry' => $entry,
                'visibleFields' => $visibleFields,
                'fieldPartial' => $fieldPartial,
                'fieldContext' => $fieldContext,
            ])
        @endforeach
    @else
        @foreach ($resolved['entries'] as $entry)
            @include('livewire.forms.partials.grouped-fields-row', [
                'entry' => $entry,
                'visibleFields' => $visibleFields,
                'fieldPartial' => $fieldPartial,
                'fieldContext' => $fieldContext,
            ])
        @endforeach
    @endif
@endforeach
