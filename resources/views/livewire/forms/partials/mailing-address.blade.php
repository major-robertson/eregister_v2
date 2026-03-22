@php
    $mailingPrefix = $isCore ? 'coreData' : 'stateData';
    $initialMailingValue = $isCore
        ? ($this->coreData['mailing_address_same'] ?? '1')
        : ($this->stateData['mailing_address_same'] ?? '1');
    $showMailingInitial = $initialMailingValue === '0' || $initialMailingValue === 0 || $initialMailingValue === false;
@endphp

<div x-data="{ showMailing: {{ $showMailingInitial ? 'true' : 'false' }} }">
    <flux:switch
        x-on:change="showMailing = $event.target.checked; $wire.set('{{ $mailingPrefix }}.mailing_address_same', $event.target.checked ? '0' : '1')"
        :checked="$showMailingInitial"
        label="I have a different mailing address"
    />

    @if ($mailingAddressField)
        <div x-show="showMailing" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="mt-6">
            <x-ui.card>
                <flux:heading size="lg" class="mb-4">Mailing Address</flux:heading>
                @include('livewire.forms.partials.fields.address', [
                    'fieldKey' => 'mailing_address',
                    'field' => $mailingAddressField,
                    'prefix' => $mailingPrefix,
                    'label' => 'Mailing Address',
                    'hideLabel' => true,
                ])
            </x-ui.card>
        </div>
    @endif
</div>
