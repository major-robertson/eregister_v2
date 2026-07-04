<div class="space-y-6" x-data="pdfCoordinateMapper($wire)">
    <div class="flex items-start justify-between gap-4">
        <div>
            <flux:heading size="xl">PDF Coordinate Mapper</flux:heading>
            <flux:text class="mt-1 text-zinc-500">
                Click anywhere on a PDF to capture FPDI coordinates (mm). Pick a template or upload any PDF —
                lien forms, government documents, anything you need to map fields on.
            </flux:text>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-4">
        {{-- Controls + pins panel --}}
        <div class="space-y-6">
            <div class="rounded-xl border border-border bg-white p-4">
                <flux:heading size="sm" class="mb-3">Source</flux:heading>

                <div class="space-y-3">
                    @foreach ($this->libraries as $key => $library)
                        <flux:field>
                            <flux:label>{{ $library['label'] }}</flux:label>
                            <flux:select variant="combobox" clearable placeholder="Pick a template..." wire:model.live="template">
                                @foreach ($library['files'] as $file)
                                    <flux:select.option value="{{ $file }}">{{ $file }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        </flux:field>
                    @endforeach

                    <flux:field>
                        <flux:label>Or upload a PDF</flux:label>
                        <input type="file" wire:model="upload" accept="application/pdf"
                            class="block w-full text-sm text-zinc-600 file:mr-3 file:rounded-lg file:border-0 file:bg-zinc-100 file:px-3 file:py-1.5 file:text-sm file:font-medium hover:file:bg-zinc-200" />
                        <flux:error name="upload" />
                        <div wire:loading wire:target="upload" class="text-xs text-zinc-500">Uploading...</div>
                    </flux:field>
                </div>
            </div>

            <div class="rounded-xl border border-border bg-white p-4" x-show="loaded" x-cloak>
                <flux:heading size="sm" class="mb-3">View</flux:heading>

                <div class="space-y-2 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-zinc-500">Page</span>
                        <span class="flex items-center gap-1">
                            <flux:button size="sm" variant="ghost" icon="chevron-left" x-on:click="prevPage()" x-bind:disabled="pageNum <= 1" />
                            <span x-text="pageNum + ' / ' + pageCount" class="tabular-nums"></span>
                            <flux:button size="sm" variant="ghost" icon="chevron-right" x-on:click="nextPage()" x-bind:disabled="pageNum >= pageCount" />
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-zinc-500">Zoom</span>
                        <span class="flex items-center gap-1">
                            <flux:button size="sm" variant="ghost" icon="minus" x-on:click="zoom(-0.25)" />
                            <span x-text="Math.round(scale * 100) + '%'" class="tabular-nums"></span>
                            <flux:button size="sm" variant="ghost" icon="plus" x-on:click="zoom(0.25)" />
                        </span>
                    </div>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" x-model="showGrid" x-on:change="drawOverlay()" class="h-4 w-4 rounded border-zinc-300 text-blue-600" />
                        <span>5mm grid overlay</span>
                    </label>
                    <label class="flex items-center gap-2" x-show="fields.length">
                        <input type="checkbox" x-model="showFields" x-on:change="drawOverlay()" class="h-4 w-4 rounded border-zinc-300 text-blue-600" />
                        <span>Existing field map (<span x-text="fields.length"></span>)</span>
                    </label>
                </div>
            </div>

            {{-- Pinned coordinates --}}
            <div class="rounded-xl border border-border bg-white p-4" x-show="pins.length" x-cloak>
                <div class="mb-3 flex items-center justify-between">
                    <flux:heading size="sm">Pins</flux:heading>
                    <flux:button size="sm" variant="ghost" x-on:click="pins = []; drawOverlay()">Clear</flux:button>
                </div>

                <div class="space-y-2">
                    <template x-for="(pin, index) in pins" :key="index">
                        <div class="rounded-lg border border-border p-2 text-xs">
                            <div class="flex items-center justify-between">
                                <input type="text" x-model="pin.label"
                                    class="w-24 rounded border-0 bg-transparent p-0 text-xs font-medium focus:ring-0" />
                                <span class="flex items-center gap-1">
                                    <span class="tabular-nums text-zinc-500"
                                        x-text="pin.x.toFixed(1) + ', ' + pin.y.toFixed(1) + (pageCount > 1 ? ' p' + pin.page : '')"></span>
                                    <button type="button" class="text-zinc-400 hover:text-red-500" x-on:click="pins.splice(index, 1); drawOverlay()">&times;</button>
                                </span>
                            </div>
                            <div class="mt-1 flex items-center gap-1">
                                <code class="block flex-1 truncate rounded bg-zinc-50 px-1.5 py-0.5 font-mono text-[10px] text-zinc-600"
                                    x-text="snippet(pin)"></code>
                                <button type="button" class="shrink-0 text-blue-600 hover:text-blue-800"
                                    x-on:click="navigator.clipboard.writeText(snippet(pin)); $flux.toast({text: 'Snippet copied', variant: 'success'})">
                                    Copy
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Resale sample renders --}}
            <div class="rounded-xl border border-border bg-white p-4">
                <flux:heading size="sm" class="mb-1">Test render (resale certs)</flux:heading>
                <flux:text class="mb-3 text-xs text-zinc-500">
                    Renders sample data through the real generation pipeline with the mm grid — open next to the
                    mapper to verify positions. MTC/SST also offer an all-tax-id-positions overlay.
                </flux:text>

                <div class="space-y-2" x-data="{ sampleState: 'TX', oos: false }">
                    <flux:select wire:ignore x-model="sampleState" size="sm">
                        @foreach ($this->sampleStates as $code => $name)
                            <option value="{{ $code }}">{{ $code }} — {{ $name }}</option>
                        @endforeach
                    </flux:select>
                    <label class="flex items-center gap-2 text-xs text-zinc-600">
                        <input type="checkbox" x-model="oos" class="h-3.5 w-3.5 rounded border-zinc-300 text-blue-600" />
                        Out-of-state tax id (tests alternate templates)
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <flux:button size="sm" variant="primary"
                            x-on:click="window.open(`{{ url('admin/tools/pdf-mapper/sample') }}/${sampleState}?grid=1${oos ? '&out_of_state=1' : ''}`, '_blank')">
                            With grid
                        </flux:button>
                        <flux:button size="sm" variant="ghost"
                            x-on:click="window.open(`{{ url('admin/tools/pdf-mapper/sample') }}/${sampleState}?grid=0${oos ? '&out_of_state=1' : ''}`, '_blank')">
                            Clean
                        </flux:button>
                        <flux:button size="sm" variant="ghost"
                            x-show="sampleState === 'MTC' || sampleState === 'SST'"
                            x-on:click="window.open(`{{ url('admin/tools/pdf-mapper/sample') }}/${sampleState}?all_tax_ids=1`, '_blank')">
                            All tax ids
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Viewer --}}
        <div class="xl:col-span-3">
            <div class="rounded-xl border border-border bg-white p-4">
                <div class="mb-3 flex flex-wrap items-center justify-between gap-2" x-show="loaded" x-cloak>
                    <flux:text class="text-sm font-medium text-text-primary" x-text="documentName"></flux:text>
                    <div class="rounded-lg bg-zinc-900 px-3 py-1.5 font-mono text-sm tabular-nums text-white">
                        <span x-text="cursor.x.toFixed(1)"></span>, <span x-text="cursor.y.toFixed(1)"></span> mm
                    </div>
                </div>

                <div x-show="!loaded" class="py-24 text-center">
                    <flux:icon name="document-magnifying-glass" class="mx-auto h-12 w-12 text-zinc-300" />
                    <flux:text class="mt-2 text-zinc-500">Pick a template or upload a PDF to start mapping.</flux:text>
                </div>

                <div x-show="loading" class="py-24 text-center" x-cloak>
                    <flux:icon name="arrow-path" class="mx-auto h-8 w-8 animate-spin text-zinc-400" />
                </div>

                <div class="overflow-auto" x-show="loaded && !loading" x-cloak>
                    <div class="relative inline-block cursor-crosshair" x-ref="stage"
                        x-on:mousemove="trackCursor($event)"
                        x-on:click="addPin($event)">
                        <canvas x-ref="pdfCanvas" class="block"></canvas>
                        <canvas x-ref="overlayCanvas" class="pointer-events-none absolute left-0 top-0"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@script
<script>
Alpine.data('pdfCoordinateMapper', ($wire) => {
// Kept OUTSIDE Alpine's reactive proxy: pdf.js objects use private slots and
// worker messaging that silently hang when accessed through a Proxy.
let pdfjs = null;
let pdfDoc = null;

return {
    loaded: false,
    loading: false,
    documentName: '',
    pageNum: 1,
    pageCount: 1,
    scale: 1.5,
    // Current page size in PDF points (1pt = 25.4/72 mm).
    pageWidthPts: 0,
    pageHeightPts: 0,
    cursor: { x: 0, y: 0 },
    pins: [],
    fields: [],
    showGrid: false,
    showFields: true,

    MM_PER_PT: 25.4 / 72,

    init() {
        Livewire.on('pdf-mapper-load', () => this.loadPdf());
    },

    async loadPdf() {
        this.loading = true;

        try {
            if (! pdfjs) {
                pdfjs = await import('https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.8.69/pdf.min.mjs');
                pdfjs.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.8.69/pdf.worker.min.mjs';
            }

            const payload = await $wire.pdfPayload();
            this.documentName = payload.name;
            this.fields = payload.fields ?? [];
            this.pins = [];

            const bytes = Uint8Array.from(atob(payload.base64), c => c.charCodeAt(0));
            pdfDoc = await pdfjs.getDocument({ data: bytes }).promise;
            this.pageCount = pdfDoc.numPages;
            this.pageNum = 1;

            await this.renderPage();
            this.loaded = true;
        } finally {
            this.loading = false;
        }
    },

    async renderPage() {
        const page = await pdfDoc.getPage(this.pageNum);
        const base = page.getViewport({ scale: 1 });
        this.pageWidthPts = base.width;
        this.pageHeightPts = base.height;

        const viewport = page.getViewport({ scale: this.scale });
        const canvas = this.$refs.pdfCanvas;
        canvas.width = viewport.width;
        canvas.height = viewport.height;

        const overlay = this.$refs.overlayCanvas;
        overlay.width = viewport.width;
        overlay.height = viewport.height;

        // intent 'print' avoids requestAnimationFrame scheduling, which
        // never fires in backgrounded tabs and stalls the render forever.
        await page.render({ canvasContext: canvas.getContext('2d'), viewport, intent: 'print' }).promise;
        this.drawOverlay();
    },

    async zoom(delta) {
        this.scale = Math.min(4, Math.max(0.5, this.scale + delta));
        await this.renderPage();
    },

    async prevPage() {
        if (this.pageNum > 1) { this.pageNum--; await this.renderPage(); }
    },

    async nextPage() {
        if (this.pageNum < this.pageCount) { this.pageNum++; await this.renderPage(); }
    },

    // Canvas px -> mm (FPDI unit, origin top-left).
    toMm(event) {
        const rect = this.$refs.pdfCanvas.getBoundingClientRect();
        const px = (event.clientX - rect.left) / rect.width;
        const py = (event.clientY - rect.top) / rect.height;

        return {
            x: px * this.pageWidthPts * this.MM_PER_PT,
            y: py * this.pageHeightPts * this.MM_PER_PT,
        };
    },

    mmToPx(xMm, yMm) {
        const canvas = this.$refs.pdfCanvas;

        return {
            x: (xMm / (this.pageWidthPts * this.MM_PER_PT)) * canvas.width,
            y: (yMm / (this.pageHeightPts * this.MM_PER_PT)) * canvas.height,
        };
    },

    trackCursor(event) {
        this.cursor = this.toMm(event);
    },

    addPin(event) {
        const point = this.toMm(event);
        this.pins.push({
            label: 'field' + (this.pins.length + 1),
            x: point.x,
            y: point.y,
            page: this.pageNum,
        });
        this.drawOverlay();
    },

    snippet(pin) {
        return '$this->writeAt($pdf, ' + pin.x.toFixed(1) + ', ' + pin.y.toFixed(1) + ', $data->' + pin.label + ');';
    },

    drawOverlay() {
        const overlay = this.$refs.overlayCanvas;
        const ctx = overlay.getContext('2d');
        ctx.clearRect(0, 0, overlay.width, overlay.height);

        if (this.showGrid) {
            const widthMm = this.pageWidthPts * this.MM_PER_PT;
            const heightMm = this.pageHeightPts * this.MM_PER_PT;
            ctx.font = '9px sans-serif';

            for (let mm = 0; mm <= widthMm; mm += 5) {
                const major = mm % 10 === 0;
                ctx.strokeStyle = major ? 'rgba(239,68,68,0.45)' : 'rgba(239,68,68,0.18)';
                ctx.lineWidth = major ? 1 : 0.5;
                const { x } = this.mmToPx(mm, 0);
                ctx.beginPath(); ctx.moveTo(x, 0); ctx.lineTo(x, overlay.height); ctx.stroke();

                if (major && mm > 0) {
                    ctx.fillStyle = 'rgba(239,68,68,0.8)';
                    ctx.fillText(String(mm), x + 2, 10);
                }
            }

            for (let mm = 0; mm <= heightMm; mm += 5) {
                const major = mm % 10 === 0;
                ctx.strokeStyle = major ? 'rgba(239,68,68,0.45)' : 'rgba(239,68,68,0.18)';
                ctx.lineWidth = major ? 1 : 0.5;
                const { y } = this.mmToPx(0, mm);
                ctx.beginPath(); ctx.moveTo(0, y); ctx.lineTo(overlay.width, y); ctx.stroke();

                if (major && mm > 0) {
                    ctx.fillStyle = 'rgba(239,68,68,0.8)';
                    ctx.fillText(String(mm), 2, y - 2);
                }
            }
        }

        if (this.showFields) {
            for (const field of this.fields) {
                if ((field.page ?? 1) !== this.pageNum) continue;
                const { x, y } = this.mmToPx(field.x, field.y);
                ctx.fillStyle = 'rgba(37,99,235,0.9)';
                ctx.beginPath(); ctx.arc(x, y, 4, 0, Math.PI * 2); ctx.fill();
                ctx.font = '10px sans-serif';
                ctx.fillText(field.name, x + 6, y - 4);
            }
        }

        for (const pin of this.pins) {
            if (pin.page !== this.pageNum) continue;
            const { x, y } = this.mmToPx(pin.x, pin.y);
            ctx.strokeStyle = 'rgba(22,163,74,1)';
            ctx.lineWidth = 1.5;
            ctx.beginPath(); ctx.moveTo(x - 6, y); ctx.lineTo(x + 6, y); ctx.stroke();
            ctx.beginPath(); ctx.moveTo(x, y - 6); ctx.lineTo(x, y + 6); ctx.stroke();
            ctx.fillStyle = 'rgba(22,163,74,1)';
            ctx.font = 'bold 10px sans-serif';
            ctx.fillText(pin.label, x + 7, y + 4);
        }
    },
};
});
</script>
@endscript
