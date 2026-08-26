<footer class="pcc-on-dark bg-[#081F3A] px-4 pb-8 pt-14 text-[#C9DAEC] md:px-8">
    <div class="mx-auto grid max-w-[1320px] grid-cols-1 gap-9 sm:grid-cols-2 lg:grid-cols-4">
        <div class="flex flex-col gap-2.5">
            <span class="pcc-display text-[22px] font-black text-white">Pitt Community College</span>
            <span class="text-[14.5px] leading-relaxed">Educating and empowering people for success — since 1961.</span>
            <address class="text-[14.5px] leading-relaxed not-italic">
                2064 Warren Drive<br>
                Winterville, NC 28590<br>
                <a href="tel:2524937200" class="text-[#FFB71B] hover:underline">252-493-7200</a>
            </address>
        </div>

        <nav class="flex flex-col items-start gap-2" aria-label="Explore">
            <span class="text-sm font-extrabold uppercase tracking-[.1em] text-white">Explore</span>
            <a href="{{ route('pcc-demo.programs') }}" class="text-[15px] text-[#C9DAEC] no-underline hover:text-white">Find a Program</a>
            <a href="{{ route('pcc-demo.admissions') }}" class="text-[15px] text-[#C9DAEC] no-underline hover:text-white">Admissions Steps</a>
            <a href="{{ route('pcc-demo.paying') }}" class="text-[15px] text-[#C9DAEC] no-underline hover:text-white">Paying for College</a>
            <a href="{{ route('pcc-demo.workforce') }}" class="text-[15px] text-[#C9DAEC] no-underline hover:text-white">Workforce &amp; Community</a>
            <button type="button" @click="openStub('Student Stories')" class="cursor-pointer text-[15px] text-[#C9DAEC] hover:text-white">Student Stories</button>
        </nav>

        <nav class="flex flex-col items-start gap-2" aria-label="Resources">
            <span class="text-sm font-extrabold uppercase tracking-[.1em] text-white">Resources</span>
            <button type="button" @click="openStub('Academic Calendar')" class="cursor-pointer text-[15px] text-[#C9DAEC] hover:text-white">Academic Calendar</button>
            <button type="button" @click="openStub('Accessibility Services')" class="cursor-pointer text-[15px] text-[#C9DAEC] hover:text-white">Accessibility Services</button>
            <button type="button" @click="openStub('Campus Map')" class="cursor-pointer text-[15px] text-[#C9DAEC] hover:text-white">Campus Map &amp; Directions</button>
            <button type="button" @click="openStub('Directory')" class="cursor-pointer text-[15px] text-[#C9DAEC] hover:text-white">Directory</button>
            <button type="button" @click="openStub('myPittCC Portal')" class="cursor-pointer text-[15px] text-[#C9DAEC] hover:text-white">myPittCC</button>
        </nav>

        <div class="flex flex-col gap-3">
            <span class="text-sm font-extrabold uppercase tracking-[.1em] text-white">Take the next step</span>
            <button type="button" @click="openStub('Apply to PCC')" class="pcc-hoverable cursor-pointer rounded bg-[#FFB71B] p-3 text-center font-extrabold text-[#0C2E52] hover:bg-[#E8A404]">Apply Now</button>
            <button type="button" @click="openStub('Request Information')" class="pcc-hoverable cursor-pointer rounded border-[1.5px] border-white/40 p-[11px] text-center font-bold text-white hover:border-white">Request Information</button>
        </div>
    </div>

    <div class="mx-auto mt-9 flex max-w-[1320px] flex-wrap justify-between gap-3 border-t border-white/15 pt-[18px] text-[13px] text-[#9FB3C8]">
        <span>&copy;2026 Pitt Community College. Redesign concept — not an official PCC website.</span>
        <span>WCAG 2.1 AA &middot; Mobile-first &middot; Modular design system</span>
    </div>
</footer>
