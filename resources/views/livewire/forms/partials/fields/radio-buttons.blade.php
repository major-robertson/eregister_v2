{{--
    Segmented radio buttons (Yes / No / option groups).

    Self-contained Tailwind styling instead of Flux's `buttons` variant:
    the selected state is driven by the native :checked input through
    Tailwind v4's `has-checked:` variant, so highlighting works
    instantly on click (no Livewire roundtrip needed) and every button
    gets identical sizing regardless of label length.

    Inputs:
      - $wireModel (string)  Livewire model path
      - $options   (array)   value => label
--}}
<div class="flex w-full flex-wrap gap-2 sm:w-auto" role="radiogroup">
    @foreach ($options as $optionValue => $optionLabel)
        <label
            wire:key="radio-{{ $wireModel }}-{{ $optionValue }}"
            class="group flex min-w-28 flex-1 cursor-pointer select-none items-center justify-center gap-2 rounded-lg border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 shadow-xs transition
                hover:border-zinc-400 hover:bg-zinc-50
                has-checked:border-primary has-checked:bg-primary has-checked:text-white has-checked:shadow-sm
                has-focus-visible:ring-2 has-focus-visible:ring-primary/50
                sm:flex-none
                dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:border-zinc-500 dark:hover:bg-zinc-700"
        >
            <input
                type="radio"
                wire:model.live="{{ $wireModel }}"
                name="{{ $wireModel }}"
                value="{{ $optionValue }}"
                class="sr-only"
            />
            <svg class="hidden size-4 shrink-0 group-has-checked:block" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
            </svg>
            {{ $optionLabel }}
        </label>
    @endforeach
</div>
