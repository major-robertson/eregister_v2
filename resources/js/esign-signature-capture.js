/*
 * Shared e-signature capture, used by resources/views/components/esign/signature-capture.blade.php:
 * type your name in a script font or draw freehand onto one 500x100 canvas, and export the same PNG
 * data URI either way.
 *
 * Registered from the bundle rather than an inline script in the component. The component often
 * first appears in a Livewire update (the resale onboarding signature step), and scripts inside
 * Livewire-morphed HTML never run, so an inline registration left it undefined (EREG-94).
 */
const register = (Alpine) => Alpine.data('esignSignatureCapture', (defaultName = '') => ({
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

        // The script fonts' stylesheet arrives with the component, so the first
        // render can fall back to a serif. Redraw once the web fonts load, so the
        // adopted image never captures the fallback.
        document.fonts?.addEventListener('loadingdone', () => this.mode === 'type' && this.renderTyped());
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

if (window.Alpine) {
    register(window.Alpine);
} else {
    document.addEventListener('alpine:init', () => register(window.Alpine));
}
