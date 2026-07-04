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

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('esignSignatureCapture', (defaultName = '') => ({
                mode: 'type',
                typedName: defaultName,
                typedFont: 'dancing-script',
                fonts: {
                    'dancing-script': "'Dancing Script'",
                    'great-vibes': "'Great Vibes'",
                    'caveat': "'Caveat'",
                },
                strokes: [],
                currentStroke: null,
                hasStrokes: false,
                ctx: null,

                get hasSignature() {
                    return this.mode === 'type' ? this.typedName.trim() !== '' : this.hasStrokes;
                },

                init() {
                    const canvas = this.$refs.canvas;
                    this.ctx = canvas.getContext('2d');
                    this.bindDrawing(canvas);
                    this.$watch('typedName', () => this.mode === 'type' && this.renderTyped());
                    this.$watch('typedFont', () => this.mode === 'type' && this.renderTyped());
                    this.renderTyped();
                },

                setMode(mode) {
                    this.mode = mode;
                    this.clearCanvas();

                    if (mode === 'type') {
                        this.renderTyped();
                    }
                },

                clearCanvas() {
                    const canvas = this.$refs.canvas;
                    this.ctx.clearRect(0, 0, canvas.width, canvas.height);
                    this.strokes = [];
                    this.currentStroke = null;
                    this.hasStrokes = false;
                },

                clear() {
                    this.clearCanvas();

                    if (this.mode === 'type') {
                        this.typedName = '';
                    }
                },

                async renderTyped() {
                    const canvas = this.$refs.canvas;
                    const family = this.fonts[this.typedFont];
                    this.ctx.clearRect(0, 0, canvas.width, canvas.height);

                    const name = this.typedName.trim();
                    if (name === '') return;

                    // Shrink until the name fits the canvas width.
                    let size = 54;
                    try { await document.fonts.load(`${size}px ${family}`); } catch (e) {}

                    this.ctx.fillStyle = '#000';

                    do {
                        this.ctx.font = `${size}px ${family}`;
                        if (this.ctx.measureText(name).width <= canvas.width - 24) break;
                        size -= 4;
                    } while (size > 18);

                    this.ctx.textBaseline = 'middle';
                    this.ctx.textAlign = 'center';
                    this.ctx.fillText(name, canvas.width / 2, canvas.height / 2);
                },

                bindDrawing(canvas) {
                    const pos = (e) => {
                        const rect = canvas.getBoundingClientRect();
                        const point = e.touches ? e.touches[0] : e;
                        return {
                            x: (point.clientX - rect.left) * (canvas.width / rect.width),
                            y: (point.clientY - rect.top) * (canvas.height / rect.height),
                        };
                    };

                    const start = (e) => {
                        if (this.mode !== 'draw') return;
                        e.preventDefault();
                        const p = pos(e);
                        this.currentStroke = [p];
                        this.ctx.lineWidth = 3;
                        this.ctx.lineCap = 'round';
                        this.ctx.lineJoin = 'round';
                        this.ctx.strokeStyle = '#000';
                        this.ctx.beginPath();
                        this.ctx.moveTo(p.x, p.y);
                    };

                    const move = (e) => {
                        if (!this.currentStroke) return;
                        e.preventDefault();
                        const p = pos(e);
                        this.currentStroke.push(p);
                        this.ctx.lineTo(p.x, p.y);
                        this.ctx.stroke();
                    };

                    const end = () => {
                        if (this.currentStroke && this.currentStroke.length > 1) {
                            this.strokes.push(this.currentStroke);
                            this.hasStrokes = true;
                        }
                        this.currentStroke = null;
                    };

                    canvas.addEventListener('mousedown', start);
                    canvas.addEventListener('mousemove', move);
                    window.addEventListener('mouseup', end);
                    canvas.addEventListener('touchstart', start, { passive: false });
                    canvas.addEventListener('touchmove', move, { passive: false });
                    canvas.addEventListener('touchend', end);
                },

                export() {
                    if (!this.hasSignature) return null;

                    return {
                        dataUrl: this.$refs.canvas.toDataURL('image/png'),
                        strokesJson: this.mode === 'draw' ? JSON.stringify(this.strokes) : null,
                        method: this.mode === 'type' ? 'typed' : 'drawn',
                        typedName: this.mode === 'type' ? this.typedName.trim() : null,
                        typedFont: this.mode === 'type' ? this.typedFont : null,
                    };
                },
            }));
        });
    </script>
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
