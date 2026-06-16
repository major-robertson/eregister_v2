@php
    /**
     * Render a single entry from grouped-fields.blade.php — either a
     * full-width single field (string key) or a side-by-side row of two
     * fields (array of keys, currently always rendered as a 2-col grid).
     *
     * Variables in scope:
     *   - $entry         (string|array)  field key, or array of field keys
     *   - $visibleFields (array)         map of fieldKey => fieldDef
     *   - $fieldPartial  (string)        partial path used to render each field
     *   - $fieldContext  (array)         base context dict merged into each field include
     */
@endphp

@if (is_array($entry))
    <div class="grid grid-cols-2 gap-4">
        @foreach ($entry as $rowKey)
            @if (array_key_exists($rowKey, $visibleFields))
                @include($fieldPartial, array_merge($fieldContext, [
                    'fieldKey' => $rowKey,
                    'field' => $visibleFields[$rowKey],
                    'subKey' => $rowKey,
                    'subField' => $visibleFields[$rowKey],
                    'drivesConditional' => $visibleFields[$rowKey]['drives_conditional'] ?? false,
                ]))
            @endif
        @endforeach
    </div>
@elseif (array_key_exists($entry, $visibleFields))
    @include($fieldPartial, array_merge($fieldContext, [
        'fieldKey' => $entry,
        'field' => $visibleFields[$entry],
        'subKey' => $entry,
        'subField' => $visibleFields[$entry],
        'drivesConditional' => $visibleFields[$entry]['drives_conditional'] ?? false,
    ]))
@endif
