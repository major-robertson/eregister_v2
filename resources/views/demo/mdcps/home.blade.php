@extends('demo.mdcps.layout')

@section('body')
    <div :style="'--school-accent:' + $store.mdcps.branding.accent">
        {{-- ============================================================ --}}
        {{-- Site temporarily unavailable state                          --}}
        {{-- ============================================================ --}}
        <div x-cloak x-show="!$store.mdcps.siteEnabled" class="flex min-h-[70vh] items-center justify-center px-4">
            <div class="max-w-md text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.34 3.94a2 2 0 013.32 0l7.2 12a2 2 0 01-1.66 3.06H4.8a2 2 0 01-1.66-3.06l7.2-12zM12 9v4m0 4h.01" />
                    </svg>
                </div>
                <h1 class="mt-6 text-2xl font-bold text-slate-900">Site temporarily unavailable</h1>
                <p class="mt-3 text-slate-600">
                    The Everglades Elementary School website is undergoing scheduled maintenance.
                    Please check back shortly. For urgent matters, call the main office at (305) 555-0142.
                </p>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- Full public site                                            --}}
        {{-- ============================================================ --}}
        <div x-cloak x-show="$store.mdcps.siteEnabled">
            @include('demo.mdcps.partials.public-header', ['active' => 'home'])

            {{-- Emergency alert banner --}}
            <div x-cloak x-show="$store.mdcps.alert.active" x-transition role="alert"
                class="border-b border-red-700 bg-danger text-white">
                <div class="mx-auto flex max-w-screen-xl items-start gap-3 px-4 py-3 sm:px-6">
                    <svg class="mt-0.5 h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div>
                        <p class="text-sm font-bold uppercase tracking-wide">Emergency Alert</p>
                        <p class="text-sm" x-text="$store.mdcps.alert.text"></p>
                    </div>
                </div>
            </div>

            {{-- District announcement (master brand navy) --}}
            <div x-cloak x-show="$store.mdcps.districtAnnouncement.active" x-transition class="border-b border-blue-100 bg-blue-50">
                <div class="mx-auto flex max-w-screen-xl items-center gap-3 px-4 py-2.5 text-sm text-[#0b3d6b] sm:px-6">
                    <span class="rounded bg-[#0b3d6b] px-2 py-0.5 text-[11px] font-semibold uppercase text-white">District</span>
                    <p x-text="$store.mdcps.districtAnnouncement.text"></p>
                </div>
            </div>

            {{-- Hero --}}
            <section class="relative overflow-hidden text-white"
                :style="`background: linear-gradient(to bottom right, #0b3d6b, ${$store.mdcps.branding.accent})`">
                <div class="mx-auto grid max-w-screen-xl items-center gap-8 px-4 py-14 sm:px-6 lg:grid-cols-2 lg:py-20">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-blue-100">Welcome to</p>
                        <h1 class="mt-2 text-3xl font-extrabold sm:text-4xl lg:text-5xl">Everglades Elementary School</h1>
                        <p class="mt-4 max-w-lg text-blue-50">
                            A nurturing, technology-forward community where every
                            <span x-text="$store.mdcps.branding.mascotName"></span> grows. Serving grades Pre-K through 5 in the heart of Miami-Dade County.
                        </p>
                        <div class="mt-7 flex flex-wrap gap-3">
                            <a href="#enroll" class="rounded-lg bg-white px-5 py-2.5 text-sm font-semibold shadow-sm transition hover:bg-blue-50"
                                :style="`color: ${$store.mdcps.branding.accent}`">Enroll Now</a>
                            <a href="{{ route('mdcps-demo.calendar') }}" class="rounded-lg border border-white/40 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-white/10">School Calendar</a>
                        </div>
                    </div>
                    <div class="relative">
                        <div class="overflow-hidden rounded-2xl border-4 border-white/20 shadow-2xl">
                            <img :src="$store.mdcps.media.src" :alt="$store.mdcps.media.alt" class="aspect-[8/5] w-full object-cover" />
                        </div>
                        <div class="absolute -bottom-4 -left-4 hidden rounded-xl bg-white px-4 py-3 text-slate-900 shadow-lg sm:block">
                            <p class="text-2xl font-extrabold" :style="`color: ${$store.mdcps.branding.accent}`">A+</p>
                            <p class="text-xs font-medium text-slate-500">State-rated school</p>
                        </div>
                    </div>
                </div>
            </section>

            <main>
                {{-- Quick links --}}
                <section class="mx-auto max-w-screen-xl px-4 py-10 sm:px-6">
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                        @php
                            $quick = [
                                ['Enroll', 'M12 4v16m8-8H4'],
                                ['Lunch Menu', 'M3 3h18v4H3zM3 10h18M7 10v11M17 10v11'],
                                ['Bus Routes', 'M8 16a2 2 0 100 4 2 2 0 000-4zm8 0a2 2 0 100 4 2 2 0 000-4zM4 16V6a2 2 0 012-2h12a2 2 0 012 2v10'],
                                ['Parent Portal', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                            ];
                        @endphp
                        @foreach ($quick as $link)
                            <button type="button" @click="showDemoNote('{{ $link[0] }}')"
                                class="group flex flex-col items-center gap-3 rounded-xl border border-slate-200 bg-white p-5 text-center shadow-sm transition hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-md">
                                <span class="flex h-11 w-11 items-center justify-center rounded-full"
                                    :style="`background: color-mix(in srgb, ${$store.mdcps.branding.accent} 12%, white); color: ${$store.mdcps.branding.accent}`">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $link[1] }}" />
                                    </svg>
                                </span>
                                <span class="text-sm font-semibold text-slate-800">{{ $link[0] }}</span>
                            </button>
                        @endforeach
                    </div>
                </section>

                <div class="mx-auto grid max-w-screen-xl gap-8 px-4 pb-12 sm:px-6 lg:grid-cols-3">
                    {{-- News / announcements --}}
                    <section class="lg:col-span-2">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-bold text-slate-900">News &amp; Announcements</h2>
                            <button type="button" @click="showDemoNote('News &amp; Announcements')" class="text-sm font-semibold hover:underline"
                                :style="`color: ${$store.mdcps.branding.accent}`">View all</button>
                        </div>
                        <div class="mt-5 grid gap-5 sm:grid-cols-2">
                            @php
                                $news = [
                                    ['Gators Win District Science Fair', 'Our 5th grade team took first place with their water-quality project.', 'Mar 4, 2026', '#0b5cab'],
                                    ['Spring Book Fair Next Week', 'Visit the media center March 16–20 to support our reading programs.', 'Mar 2, 2026', '#16a34a'],
                                    ['New After-School Coding Club', 'Sign-ups are open for our Tuesday robotics and coding club.', 'Feb 26, 2026', '#f97316'],
                                    ['Volunteer of the Month', 'Congratulations to Ms. Rivera for 200 hours of dedicated service.', 'Feb 20, 2026', '#7c3aed'],
                                ];
                            @endphp
                            @foreach ($news as $article)
                                <article class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md">
                                    <div class="h-1.5" style="background: {{ $article[3] }}"></div>
                                    <div class="p-5">
                                        <p class="text-xs font-medium text-slate-400">{{ $article[2] }}</p>
                                        <h3 class="mt-1 font-semibold text-slate-900">{{ $article[0] }}</h3>
                                        <p class="mt-2 text-sm text-slate-600">{{ $article[1] }}</p>
                                        <button type="button" @click="showDemoNote('{{ $article[0] }}')"
                                            class="mt-3 inline-block text-sm font-semibold hover:underline"
                                            :style="`color: ${$store.mdcps.branding.accent}`">Read more &rarr;</button>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>

                    {{-- Upcoming events preview (multiple) --}}
                    <section id="calendar">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-bold text-slate-900">Upcoming Events</h2>
                            <a href="{{ route('mdcps-demo.calendar') }}" class="text-sm font-semibold hover:underline" :style="`color: ${$store.mdcps.branding.accent}`">View all</a>
                        </div>
                        <div class="mt-5 space-y-3"
                            x-data="{ pretty(d) { return new Date(d + 'T00:00:00').toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' }); }, monthAbbr(d) { return new Date(d + 'T00:00:00').toLocaleDateString('en-US', { month: 'short' }); }, dayNum(d) { return new Date(d + 'T00:00:00').toLocaleDateString('en-US', { day: 'numeric' }); } }">
                            <template x-for="event in $store.mdcps.upcoming(3)" :key="event.id">
                                <div class="flex gap-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                                    <div class="flex h-14 w-14 flex-shrink-0 flex-col items-center justify-center rounded-lg text-white" :style="`background: ${$store.mdcps.branding.accent}`">
                                        <span class="text-[10px] font-semibold uppercase" x-text="monthAbbr(event.date)"></span>
                                        <span class="text-xl font-extrabold leading-none" x-text="dayNum(event.date)"></span>
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="font-semibold text-slate-900" x-text="event.title"></h3>
                                        <p class="mt-0.5 text-xs text-slate-500">
                                            <span x-text="pretty(event.date)"></span>
                                            <template x-if="event.time"><span> &middot; <span x-text="event.time"></span></span></template>
                                        </p>
                                        <p class="text-xs text-slate-500" x-show="event.location" x-text="event.location"></p>
                                    </div>
                                </div>
                            </template>
                            <p x-show="!$store.mdcps.events.length" class="rounded-xl border border-dashed border-slate-300 px-4 py-6 text-center text-sm text-slate-400">
                                No upcoming events scheduled.
                            </p>
                            <a href="{{ route('mdcps-demo.calendar') }}"
                                class="mt-1 inline-flex items-center gap-1 text-sm font-semibold hover:underline" :style="`color: ${$store.mdcps.branding.accent}`">View full calendar &rarr;</a>
                        </div>
                    </section>
                </div>

                {{-- Staff / contact --}}
                <section class="border-t border-slate-200 bg-white">
                    <div class="mx-auto grid max-w-screen-xl gap-8 px-4 py-12 sm:px-6 lg:grid-cols-2">
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">Meet Our Leadership</h2>
                            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                                @php
                                    $staff = [
                                        ['Dr. Alicia Moreno', 'Principal', 'AM'],
                                        ['Mr. James Carter', 'Assistant Principal', 'JC'],
                                        ['Ms. Priya Patel', 'Counselor', 'PP'],
                                        ['Ms. Tanya Brooks', 'Office Manager', 'TB'],
                                    ];
                                @endphp
                                @foreach ($staff as $person)
                                    <div class="flex items-center gap-3 rounded-xl border border-slate-200 p-3">
                                        <div class="flex h-11 w-11 items-center justify-center rounded-full text-sm font-bold"
                                            :style="`background: color-mix(in srgb, ${$store.mdcps.branding.accent} 12%, white); color: ${$store.mdcps.branding.accent}`">{{ $person[2] }}</div>
                                        <div>
                                            <p class="text-sm font-semibold text-slate-900">{{ $person[0] }}</p>
                                            <p class="text-xs text-slate-500">{{ $person[1] }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div id="enroll" class="rounded-2xl bg-slate-50 p-6">
                            <h2 class="text-xl font-bold text-slate-900">Contact Us</h2>
                            <dl class="mt-4 space-y-3 text-sm text-slate-700">
                                <div class="flex gap-3"><dt class="w-20 font-semibold text-slate-500">Address</dt><dd>1200 Wetland Way, Miami, FL 33199</dd></div>
                                <div class="flex gap-3"><dt class="w-20 font-semibold text-slate-500">Phone</dt><dd>(305) 555-0142</dd></div>
                                <div class="flex gap-3"><dt class="w-20 font-semibold text-slate-500">Hours</dt><dd>Mon–Fri, 7:30 AM – 3:30 PM</dd></div>
                                <div class="flex gap-3"><dt class="w-20 font-semibold text-slate-500">Email</dt><dd>office@everglades-demo.k12.fl.us</dd></div>
                            </dl>
                            <button type="button" @click="showDemoNote('Send a Message')"
                                class="mt-5 inline-block rounded-lg px-5 py-2.5 text-sm font-semibold text-white transition hover:opacity-90"
                                :style="`background: ${$store.mdcps.branding.accent}`">Send a Message</button>
                        </div>
                    </div>
                </section>
            </main>

            {{-- Footer (master brand navy) --}}
            <footer class="bg-[#0b3d6b] text-blue-100">
                <div class="mx-auto max-w-screen-xl px-4 py-10 sm:px-6">
                    <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <p class="text-lg font-bold text-white">Everglades Elementary</p>
                            <p class="mt-2 text-sm">A Miami-Dade County Public School. Home of the <span x-text="$store.mdcps.branding.mascotName + 's'"></span>.</p>
                        </div>
                        <div>
                            <p class="font-semibold text-white">Quick Links</p>
                            <ul class="mt-3 space-y-2 text-sm">
                                <li><button type="button" @click="showDemoNote('Enroll')" class="hover:text-white">Enroll</button></li>
                                <li><a href="{{ route('mdcps-demo.calendar') }}" class="hover:text-white">Calendar</a></li>
                                <li><button type="button" @click="showDemoNote('Staff Directory')" class="hover:text-white">Staff Directory</button></li>
                            </ul>
                        </div>
                        <div>
                            <p class="font-semibold text-white">District</p>
                            <ul class="mt-3 space-y-2 text-sm">
                                <li><button type="button" @click="showDemoNote('M-DCPS Home')" class="hover:text-white">M-DCPS Home</button></li>
                                <li><button type="button" @click="showDemoNote('Board of Education')" class="hover:text-white">Board of Education</button></li>
                                <li><button type="button" @click="showDemoNote('Superintendent')" class="hover:text-white">Superintendent</button></li>
                            </ul>
                        </div>
                        <div>
                            <p class="font-semibold text-white">Accessibility</p>
                            <ul class="mt-3 space-y-2 text-sm">
                                <li><button type="button" @click="showDemoNote('Accessibility Statement')" class="hover:text-white">Accessibility Statement</button></li>
                                <li><button type="button" @click="showDemoNote('Non-Discrimination Policy')" class="hover:text-white">Non-Discrimination Policy</button></li>
                                <li><button type="button" @click="showDemoNote('Privacy')" class="hover:text-white">Privacy</button></li>
                                <li><button type="button" @click="showDemoNote('Request Translation')" class="hover:text-white">Request Translation</button></li>
                            </ul>
                        </div>
                    </div>
                    <div class="mt-8 flex flex-col gap-2 border-t border-white/10 pt-6 text-xs sm:flex-row sm:items-center sm:justify-between">
                        <p>&copy; {{ date('Y') }} Miami-Dade County Public Schools. All rights reserved.</p>
                        <p>Everglades Elementary School</p>
                    </div>
                </div>
            </footer>
        </div>
    </div>
@endsection
