@php
    $ext = '<svg class="inline-block flex-none" width="11" height="11" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 3h7v7M13 3 3 13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>';
@endphp

<footer class="cc-on-dark bg-[#0B3A4E] text-[#C9DAE1]">
    <div class="mx-auto max-w-[1440px] px-4 pb-8 pt-12 md:px-8 xl:px-12 xl:pt-14">
        <div class="grid grid-cols-2 gap-x-8 gap-y-10 border-b border-[#9CC3D2]/25 pb-10 md:grid-cols-3 xl:grid-cols-[1.4fr_1fr_1fr_1fr_1fr] xl:gap-10">
            <div class="col-span-2 flex flex-col gap-3.5 md:col-span-3 xl:col-span-1">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('img/demos/clay-county/seal-clay-county.png') }}" alt="Seal of Clay County, Missouri" class="h-10 w-10 rounded-full bg-[#FAF7F0]" width="40" height="40">
                    <span>
                        <span class="block text-base font-extrabold text-[#FAF7F0]">Clay County Parks</span>
                        <span class="block text-[11px] font-semibold tracking-[.05em] text-[#9CC3D2] uppercase">Recreation &amp; Historic Sites</span>
                    </span>
                </div>
                <p class="m-0 max-w-[34ch] text-sm leading-relaxed">
                    17201 Paradesian<br>
                    Smithville, MO 64089<br>
                    <a href="tel:816-407-3400" class="text-[#E7C55C]">816-407-3400</a> ·
                    <a href="mailto:parks@claycountymo.gov" class="text-[#E7C55C]">parks@claycountymo.gov</a>
                </p>
                <p class="m-0 max-w-[34ch] text-[13px] leading-relaxed text-[#9CC3D2]">
                    Historic Sites office: 21216 James Farm Rd, Kearney ·
                    <a href="tel:816-736-8500" class="text-[#C9DAE1]">816-736-8500</a>
                </p>
            </div>

            <nav aria-label="Footer — Explore">
                <p class="mb-3.5 text-xs font-extrabold tracking-[.1em] text-[#9CC3D2] uppercase">Explore</p>
                <ul class="m-0 flex list-none flex-col gap-2.5 p-0 text-[14.5px]">
                    <li><a href="{{ route('clay-demo.smithville-lake') }}" class="text-[#C9DAE1] no-underline hover:text-[#FAF7F0] hover:underline">Smithville Lake</a></li>
                    <li><a href="{{ route('clay-demo.trails') }}" class="text-[#C9DAE1] no-underline hover:text-[#FAF7F0] hover:underline">Trails</a></li>
                    <li><a href="{{ route('clay-demo.historic-sites') }}" class="text-[#C9DAE1] no-underline hover:text-[#FAF7F0] hover:underline">Historic Sites</a></li>
                    <li><a href="{{ route('clay-demo.smithville-lake') }}#beaches" class="text-[#C9DAE1] no-underline hover:text-[#FAF7F0] hover:underline">Beaches &amp; Marinas</a></li>
                    <li><a href="{{ route('clay-demo.explore') }}" class="text-[#C9DAE1] no-underline hover:text-[#FAF7F0] hover:underline">Parks Directory</a></li>
                    <li><a href="{{ route('clay-demo.events') }}" class="text-[#C9DAE1] no-underline hover:text-[#FAF7F0] hover:underline">Events</a></li>
                </ul>
            </nav>

            <nav aria-label="Footer — Plan Your Visit">
                <p class="mb-3.5 text-xs font-extrabold tracking-[.1em] text-[#9CC3D2] uppercase">Plan Your Visit</p>
                <ul class="m-0 flex list-none flex-col gap-2.5 p-0 text-[14.5px]">
                    <li><button type="button" @click="openWebtrac()" class="text-[#C9DAE1] hover:text-[#FAF7F0] hover:underline">Reservations</button></li>
                    <li><button type="button" @click="alertsOpen = true" class="text-[#C9DAE1] hover:text-[#FAF7F0] hover:underline">Lake Conditions</button></li>
                    <li><button type="button" @click="alertsOpen = true" class="text-[#C9DAE1] hover:text-[#FAF7F0] hover:underline">Alerts &amp; Closures</button></li>
                    <li><a href="{{ route('clay-demo.plan-your-visit') }}" class="text-[#C9DAE1] no-underline hover:text-[#FAF7F0] hover:underline">Hours &amp; Rules</a></li>
                    <li><a href="{{ route('clay-demo.plan-your-visit') }}#faq" class="text-[#C9DAE1] no-underline hover:text-[#FAF7F0] hover:underline">FAQs</a></li>
                </ul>
            </nav>

            <nav aria-label="Footer — Resources">
                <p class="mb-3.5 text-xs font-extrabold tracking-[.1em] text-[#9CC3D2] uppercase">Resources</p>
                <ul class="m-0 flex list-none flex-col gap-2.5 p-0 text-[14.5px]">
                    <li><a href="https://www.claycountymo.gov/DocumentCenter/View/776" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-[#C9DAE1] no-underline hover:text-[#FAF7F0] hover:underline">Trail Maps {!! $ext !!}<span class="sr-only">(opens external site)</span></a></li>
                    <li><a href="https://www.claycountymo.gov/167/Beaches" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-[#C9DAE1] no-underline hover:text-[#FAF7F0] hover:underline">Water Quality {!! $ext !!}<span class="sr-only">(opens external site)</span></a></li>
                    <li><a href="https://www.claycountymo.gov/165/Parks-Recreation" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-[#C9DAE1] no-underline hover:text-[#FAF7F0] hover:underline">Parks Master Plan {!! $ext !!}<span class="sr-only">(opens external site)</span></a></li>
                    <li><button type="button" @click="openNotify()" class="text-[#C9DAE1] hover:text-[#FAF7F0] hover:underline">Alert Notifications</button></li>
                </ul>
            </nav>

            <nav aria-label="Footer — About">
                <p class="mb-3.5 text-xs font-extrabold tracking-[.1em] text-[#9CC3D2] uppercase">About</p>
                <ul class="m-0 flex list-none flex-col gap-2.5 p-0 text-[14.5px]">
                    <li><a href="https://www.claycountymo.gov/165/Parks-Recreation" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-[#C9DAE1] no-underline hover:text-[#FAF7F0] hover:underline">The Department {!! $ext !!}<span class="sr-only">(opens external site)</span></a></li>
                    <li><a href="mailto:parks@claycountymo.gov" class="text-[#C9DAE1] no-underline hover:text-[#FAF7F0] hover:underline">Contact Us</a></li>
                    <li><a href="https://www.claycountymo.gov/" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-[#C9DAE1] no-underline hover:text-[#FAF7F0] hover:underline">Clay County, MO {!! $ext !!}<span class="sr-only">(opens external site)</span></a></li>
                    <li><a href="https://www.visitclaymo.com/" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-[#C9DAE1] no-underline hover:text-[#FAF7F0] hover:underline">Visit Clay MO {!! $ext !!}<span class="sr-only">(opens external site)</span></a></li>
                    <li><a href="https://moclaycountyweb.myvscloud.com/webtrac/web/splash.html" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-[#C9DAE1] no-underline hover:text-[#FAF7F0] hover:underline">WebTrac Reservations {!! $ext !!}<span class="sr-only">(opens external site)</span></a></li>
                </ul>
            </nav>
        </div>

        <div class="flex flex-col justify-between gap-2 pt-6 text-[13px] text-[#9CC3D2] md:flex-row">
            <span>© 2026 Clay County, Missouri · Parks, Recreation &amp; Historic Sites</span>
            <span>Design concept for RFP 78-26 — all conditions and events shown are prototype data</span>
        </div>
    </div>
</footer>
