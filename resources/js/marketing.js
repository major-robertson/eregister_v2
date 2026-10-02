/*
 * Marketing pages (layouts/landing and layouts/government) run on this
 * Alpine-only bundle instead of Flux + Livewire. Pages that render a Livewire
 * component set @section('livewire', true) and get @fluxScripts instead, whose
 * Livewire build ships its own Alpine; never load both on one page.
 */
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import intersect from '@alpinejs/intersect';
import { onCLS, onINP, onLCP } from 'web-vitals';
import lienDeadlineCalculator from './lien-deadline-calculator';

Alpine.plugin(collapse);
Alpine.plugin(intersect);

// The free lien deadline calculator on /liens/deadline-calculator and the state pages.
Alpine.data('lienDeadlineCalculator', lienDeadlineCalculator);

window.Alpine = Alpine;
Alpine.start();

/*
 * Core Web Vitals from real visitors, sent to GA4 through the gtag() stub in
 * partials/head. The stub queues on dataLayer, so metrics reported before the
 * deferred gtag.js arrives are sent once it loads. Off production the stub is
 * a no-op.
 */
function sendToAnalytics(metric) {
    if (typeof window.gtag !== 'function') {
        return;
    }

    window.gtag('event', metric.name, {
        // CLS is a unitless fraction; scale it so GA4 keeps the precision.
        value: Math.round(metric.name === 'CLS' ? metric.delta * 1000 : metric.delta),
        metric_id: metric.id,
        metric_rating: metric.rating,
        non_interaction: true,
    });
}

onLCP(sendToAnalytics);
onINP(sendToAnalytics);
onCLS(sendToAnalytics);
