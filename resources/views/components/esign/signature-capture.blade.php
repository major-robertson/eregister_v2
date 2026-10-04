{{--
    Shared e-signature capture: type your name (script font of choice) or
    draw freehand — both render onto the same 500x100 canvas and export an
    identical PNG data URI, so every consumer (resale certs, esign signing)
    gets the same artifact regardless of method.

    Props:
      default-name : prefill for the typed-name input
      ref-name     : parent $refs key for reading state via Alpine.$data()

    Parent reads Alpine.$data($refs.<refName>) → { hasSignature, export() }
    where export() returns {dataUrl, strokesJson, method, typedName, typedFont}
    or null while empty.
--}}
@props(['defaultName' => '', 'refName' => 'signatureCapture'])

@once
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=caveat:700|dancing-script:600|great-vibes:400" />

@endonce

<div x-data="esignSignatureCapture(@js($defaultName))" x-ref="{{ $refName }}" class="space-y-3">
    {{-- Method tabs --}}
    <div class="inline-flex rounded-lg border border-zinc-200 p-0.5 text-sm">
        <button type="button" x-on:click="setMode('type')"
            x-bind:class="mode === 'type' ? 'bg-zinc-100 font-medium text-zinc-900' : 'text-zinc-500'"
            class="rounded-md px-3 py-1.5">
            Type
        </button>
        <button type="button" x-on:click="setMode('draw')"
            x-bind:class="mode === 'draw' ? 'bg-zinc-100 font-medium text-zinc-900' : 'text-zinc-500'"
            class="rounded-md px-3 py-1.5">
            Draw
        </button>
    </div>

    {{-- Typed controls --}}
    <div x-show="mode === 'type'" class="space-y-2">
        <input type="text" x-model="typedName" placeholder="Type your full legal name"
            class="w-full max-w-md rounded-lg border-zinc-300 text-sm focus:border-blue-500 focus:ring-blue-500" />

        <div class="flex flex-wrap gap-2">
            <template x-for="(family, key) in fonts" :key="key">
                <label
                    class="cursor-pointer rounded-lg border px-3 py-1.5"
                    x-bind:class="typedFont === key ? 'border-blue-500 bg-blue-50' : 'border-zinc-200'"
                >
                    <input type="radio" x-model="typedFont" x-bind:value="key" class="sr-only" />
                    <span x-bind:style="`font-family: ${family}; font-size: 1.35rem; line-height: 1;`"
                        x-text="typedName.trim() || 'Signature'"></span>
                </label>
            </template>
        </div>
    </div>

    <div x-show="mode === 'draw'">
        <flux:text class="text-sm text-zinc-500">
            Draw your signature below with your mouse or finger.
        </flux:text>
    </div>

    {{-- Fixed 500x100: the 5:1 ratio is baked into PDF stamping. --}}
    <div class="inline-block rounded-lg border-2 border-dashed border-zinc-300 bg-white">
        <canvas x-ref="canvas" width="500" height="100"
            x-bind:class="mode === 'draw' ? 'cursor-crosshair' : ''"
            class="max-w-full touch-none"></canvas>
    </div>

    <div>
        <flux:button type="button" variant="ghost" size="sm" x-on:click="clear()">Clear</flux:button>
    </div>
</div>
