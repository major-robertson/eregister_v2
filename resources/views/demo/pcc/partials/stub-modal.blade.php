{{--
    "Demo preview" modal — opens whenever the visitor clicks a destination
    that exists in the production sitemap but isn't part of this demo's
    eight built pages, instead of leading to a dead link.
--}}
<div x-cloak x-show="stub" x-pcc-trap="stub"
    @click="stub = null"
    class="fixed inset-0 z-[100] flex items-center justify-center bg-[rgba(8,31,58,.6)] p-6"
    role="dialog" aria-modal="true" aria-labelledby="pcc-stub-title">
    <div @click.stop
        class="pcc-anim-up flex w-full max-w-[440px] flex-col gap-3.5 rounded-[14px] bg-white p-9 shadow-[0_30px_80px_rgba(8,31,58,.45)]">
        <span class="self-start rounded bg-[#FFF4DC] px-2.5 py-[5px] text-xs font-extrabold uppercase tracking-[.1em] text-[#7A5A00]">Demo preview</span>
        <h2 id="pcc-stub-title" x-text="stub" class="pcc-display m-0 text-2xl font-extrabold text-[#0C2E52]"></h2>
        <p class="m-0 text-[15.5px] leading-relaxed text-[#4A5A6A]">
            This page is planned for a later phase of the demo. For now, explore the built pages:
            Home, Program Finder, Program Pages, Admissions, Paying for College, Student Life,
            Workforce &amp; Community, and About PCC.
        </p>
        <div class="mt-1.5 flex items-center gap-2.5">
            <button type="button" @click="stub = null" data-autofocus
                class="pcc-hoverable cursor-pointer rounded bg-[#0C2E52] px-5 py-[11px] font-bold text-white hover:bg-[#123E6B]">
                Got it
            </button>
            <a href="{{ route('pcc-demo.home') }}" class="px-2 py-[11px] font-bold text-[#0C5DA5]">Back to homepage</a>
        </div>
    </div>
</div>
