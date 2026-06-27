{{--
    Renders a field-level "from → to" diff list for an application-edit audit
    event. Values are stored raw on the event; this is where they get formatted
    for humans (cents → $, booleans → Yes/No, enums → labels, dates → M j, Y).

    @param array $changes  field => ['from' => mixed, 'to' => mixed]
--}}
@php
    $labels = [
        // project / party (shared key — heading provides the context)
        'name' => 'Name',
        'job_number' => 'Job number',
        'provided_type' => 'What you provided',
        'hired_by' => 'Who hired you',
        'claimant_type' => 'Claimant type',
        'property_context' => 'Property context',
        'property_class' => 'Property class',
        'jobsite_address1' => 'Jobsite address',
        'jobsite_address2' => 'Jobsite address 2',
        'jobsite_city' => 'Jobsite city',
        'jobsite_state' => 'Jobsite state',
        'jobsite_zip' => 'Jobsite ZIP',
        'jobsite_county' => 'Jobsite county',
        'legal_description' => 'Legal description',
        'apn' => 'APN',
        'first_furnish_date' => 'First furnish date',
        'last_furnish_date' => 'Last furnish date',
        'completion_date' => 'Completion date',
        'noc_status' => 'NOC status',
        'noc_recorded_at' => 'NOC recorded',
        'base_contract_amount_cents' => 'Base contract',
        'change_orders_cents' => 'Change orders',
        'credits_deductions_cents' => 'Credits / deductions',
        'payments_received_cents' => 'Payments received',
        'uncompleted_work_cents' => 'Uncompleted work',
        'owner_is_tenant' => 'Owner is tenant',
        'has_written_contract' => 'Written contract',
        // filing
        'amount_claimed_cents' => 'Claim amount',
        'description_of_work' => 'Description of work',
        'jurisdiction_state' => 'Jurisdiction state',
        'jurisdiction_county' => 'Jurisdiction county',
        'service_level' => 'Service level',
        // party
        'role' => 'Role',
        'company_name' => 'Company name',
        'address1' => 'Address',
        'address2' => 'Address 2',
        'city' => 'City',
        'state' => 'State',
        'zip' => 'ZIP',
        'email' => 'Email',
        'phone' => 'Phone',
    ];

    $format = function (string $field, $value) {
        if ($value === null || $value === '') {
            return '—';
        }

        if (str_ends_with($field, '_cents')) {
            return '$' . number_format(((int) $value) / 100, 2);
        }

        if (in_array($field, ['owner_is_tenant', 'has_written_contract'], true)) {
            return $value ? 'Yes' : 'No';
        }

        if ($field === 'claimant_type') {
            return \App\Domains\Lien\Enums\ClaimantType::tryFrom((string) $value)?->label() ?? $value;
        }
        if ($field === 'noc_status') {
            return \App\Domains\Lien\Enums\NocStatus::tryFrom((string) $value)?->label() ?? $value;
        }
        if ($field === 'service_level') {
            return \App\Domains\Lien\Enums\ServiceLevel::tryFrom((string) $value)?->label() ?? $value;
        }
        if ($field === 'role') {
            return \App\Domains\Lien\Enums\PartyRole::tryFrom((string) $value)?->label() ?? $value;
        }

        if (str_ends_with($field, '_date') || $field === 'noc_recorded_at') {
            try {
                return \Illuminate\Support\Carbon::parse($value)->format('M j, Y');
            } catch (\Throwable) {
                return $value;
            }
        }

        return $value;
    };
@endphp

@if (! empty($changes))
<ul class="mt-1 space-y-0.5 text-xs text-gray-600">
    @foreach ($changes as $field => $change)
    <li>
        <span class="font-medium">{{ $labels[$field] ?? \Illuminate\Support\Str::headline($field) }}:</span>
        <span class="text-gray-400">{{ $format($field, $change['from'] ?? null) }}</span>
        &rarr;
        <span>{{ $format($field, $change['to'] ?? null) }}</span>
    </li>
    @endforeach
</ul>
@endif
