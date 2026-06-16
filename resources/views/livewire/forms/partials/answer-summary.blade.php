@php
    /**
     * Shape-aware answer summary. Renders a coreData / stateData blob:
     *
     *   - applies_* values ({anywhere, states[]})  → "Yes — California, Texas"
     *   - matrix values ({CA: v, TX: v})           → one line per state
     *   - repeater rows (locations, people, ...)   → compact row list
     *   - address composites                        → joined single line
     *   - scalars                                   → label/value pairs
     *
     * Values that look like Laravel encrypted payloads are masked — the
     * admin view receives raw (encrypted-at-rest) data for sensitive
     * fields and must never print ciphertext.
     *
     * Inputs: $data (array), optional $exclude (array of keys), optional
     * $stripPrefix (e.g. 'ca_' on a state card where the state is implied).
     */
    $exclude = $exclude ?? [];
    $stripPrefix = $stripPrefix ?? null;

    $looksEncrypted = fn ($v) => is_string($v) && str_starts_with($v, 'eyJpdiI6');

    $makeLabel = function (string $key) use ($stripPrefix) {
        foreach (array_filter([$stripPrefix, 'matrix_', 'applies_']) as $prefix) {
            if (str_starts_with($key, $prefix)) {
                $key = substr($key, strlen($prefix));
                break;
            }
        }

        $label = \Illuminate\Support\Str::headline($key);

        // Headline() lowercases acronyms ("Fein", "Ssn") — restore them.
        $acronyms = ['Fein' => 'FEIN', 'Ein' => 'EIN', 'Ssn' => 'SSN', 'Dba' => 'DBA', 'Naics' => 'NAICS', 'Abc' => 'ABC', 'Dmv' => 'DMV', 'Sos' => 'SOS', 'Cdtfa' => 'CDTFA', 'Sla' => 'SLA'];
        $words = array_map(fn ($w) => $acronyms[$w] ?? $w, explode(' ', $label));

        return implode(' ', $words);
    };

    $displayScalar = function ($v) use ($looksEncrypted) {
        if ($looksEncrypted($v)) {
            return '•••• (encrypted)';
        }
        if (is_bool($v)) {
            return $v ? 'Yes' : 'No';
        }
        if ($v === '1') {
            return 'Yes';
        }
        if ($v === '0') {
            return 'No';
        }
        // Option keys like "sole_prop" / "new_business" read poorly raw.
        if (is_string($v) && preg_match('/^[a-z][a-z0-9]*(_[a-z0-9]+)+$/', $v)) {
            return \Illuminate\Support\Str::headline($v);
        }

        return (string) $v;
    };

    $stateName = fn ($code) => config('states.'.$code, $code);

    $isAddress = fn ($v) => is_array($v) && (array_key_exists('line1', $v) || array_key_exists('city', $v));
    $formatAddress = function (array $a) {
        return collect([$a['line1'] ?? null, $a['line2'] ?? null, $a['city'] ?? null, $a['state'] ?? null, $a['zip'] ?? null])
            ->filter()
            ->implode(', ');
    };

    $isAppliesValue = fn ($v) => is_array($v) && array_key_exists('anywhere', $v) && array_key_exists('states', $v);

    // A matrix value is an assoc array whose keys are 2-letter state codes.
    $isMatrixValue = function ($v) {
        if (! is_array($v) || $v === [] || array_is_list($v)) {
            return false;
        }
        foreach (array_keys($v) as $k) {
            if (! is_string($k) || strlen($k) !== 2 || config('states.'.$k) === null) {
                return false;
            }
        }

        return true;
    };

    $isRowList = fn ($v) => is_array($v) && $v !== [] && array_is_list($v) && is_array($v[0] ?? null);
@endphp

<dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    @foreach ($data as $key => $value)
        @continue(in_array($key, $exclude, true))
        @continue($value === null || $value === '' || $value === [])

        @php $label = $makeLabel((string) $key); @endphp

        @if ($isAppliesValue($value))
            <div>
                <dt class="text-sm text-text-secondary">{{ $label }}</dt>
                <dd class="font-medium text-text-primary">
                    @if (($value['anywhere'] ?? null) === '1')
                        Yes — {{ collect($value['states'] ?? [])->map($stateName)->implode(', ') ?: 'no states selected' }}
                    @elseif (($value['anywhere'] ?? null) === '0')
                        No
                    @else
                        —
                    @endif
                </dd>
            </div>
        @elseif ($isAddress($value))
            @continue($formatAddress($value) === '')
            <div>
                <dt class="text-sm text-text-secondary">{{ $label }}</dt>
                <dd class="font-medium text-text-primary">{{ $formatAddress($value) }}</dd>
            </div>
        @elseif ($isMatrixValue($value))
            <div class="sm:col-span-2">
                <dt class="text-sm text-text-secondary">{{ $label }}</dt>
                <dd class="mt-1 grid grid-cols-1 gap-1 sm:grid-cols-3">
                    @foreach ($value as $code => $cell)
                        <span class="text-sm">
                            <span class="text-text-secondary">{{ $stateName($code) }}:</span>
                            <span class="font-medium text-text-primary">{{ $displayScalar($cell) ?: '-' }}</span>
                        </span>
                    @endforeach
                </dd>
            </div>
        @elseif ($isRowList($value))
            <div class="sm:col-span-2">
                <dt class="text-sm text-text-secondary">{{ $label }} ({{ count($value) }})</dt>
                <dd class="mt-1 space-y-1">
                    @foreach ($value as $row)
                        @php
                            $rowParts = collect($row)
                                ->except(['_id'])
                                ->map(function ($v) use ($displayScalar, $isAddress, $formatAddress, $looksEncrypted) {
                                    if ($isAddress($v)) {
                                        return $formatAddress($v);
                                    }
                                    if (is_array($v)) {
                                        return null;
                                    }
                                    if ($looksEncrypted($v)) {
                                        return null; // Never leak ciphertext into row summaries.
                                    }

                                    return $displayScalar($v);
                                })
                                ->filter(fn ($v) => $v !== null && $v !== '')
                                ->take(4)
                                ->implode(' · ');
                        @endphp
                        <p class="text-sm font-medium text-text-primary">{{ $rowParts ?: '—' }}</p>
                    @endforeach
                </dd>
            </div>
        @elseif (is_array($value))
            {{-- Unknown associative shape: render leaf key/value pairs. --}}
            <div class="sm:col-span-2">
                <dt class="text-sm text-text-secondary">{{ $label }}</dt>
                <dd class="mt-1 space-y-0.5">
                    @foreach ($value as $subKey => $subValue)
                        @if (! is_array($subValue) && $subValue !== null && $subValue !== '')
                            <p class="text-sm">
                                <span class="text-text-secondary">{{ \Illuminate\Support\Str::headline((string) $subKey) }}:</span>
                                <span class="font-medium text-text-primary">{{ $displayScalar($subValue) }}</span>
                            </p>
                        @endif
                    @endforeach
                </dd>
            </div>
        @else
            <div>
                <dt class="text-sm text-text-secondary">{{ $label }}</dt>
                <dd class="font-medium text-text-primary break-words">{{ $displayScalar($value) ?: '-' }}</dd>
            </div>
        @endif
    @endforeach
</dl>
