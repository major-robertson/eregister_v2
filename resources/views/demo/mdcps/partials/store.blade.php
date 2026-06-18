{{-- Shared client-side demo state for the MDCPS sandbox. --}}
{{-- All persistence is localStorage only; nothing touches the server. --}}
<script>
    (function () {
        const STORAGE_KEY = 'mdcps.demo.v1';

        // Real, license-safe stock photos served from the Unsplash CDN
        // (Unsplash License: free to use, no attribution required).
        function unsplash(id) {
            return 'https://images.unsplash.com/photo-' + id + '?auto=format&fit=crop&w=1200&q=80';
        }

        const heroImage = {
            src: unsplash('1740635341299-3b8e3490f546'),
            alt: 'A bright elementary classroom full of desks ready for students.',
            label: 'Welcoming Classroom',
        };

        const sampleImages = [
            { id: 'classroom', label: 'Welcoming Classroom', src: heroImage.src, alt: heroImage.alt },
            { id: 'stem', label: 'STEM Lab', src: unsplash('1758685734153-132c8620c1bd'), alt: 'Students in lab coats conducting a hands-on science experiment.' },
            { id: 'reading', label: 'Reading Corner', src: unsplash('1779703354655-71880dce6a17'), alt: 'Young students reading books together on the classroom floor.' },
            { id: 'arts', label: 'Art Studio', src: unsplash('1536221993589-9edbbca2c7fc'), alt: 'A young student painting with watercolors in art class.' },
        ];

        // School-identity presets. The district master brand (navy header +
        // wordmark) stays fixed; each school chooses its own accent + mascot.
        const accentPresets = [
            { name: 'Ocean Blue', value: '#0b5cab' },
            { name: 'Gator Green', value: '#16a34a' },
            { name: 'Sunset Orange', value: '#ea580c' },
            { name: 'Royal Purple', value: '#7c3aed' },
        ];

        const mascotPresets = [
            { name: 'Gator', emoji: '🐊' },
            { name: 'Eagle', emoji: '🦅' },
            { name: 'Dolphin', emoji: '🐬' },
            { name: 'Shark', emoji: '🦈' },
            { name: 'Tiger', emoji: '🐯' },
            { name: 'Bee', emoji: '🐝' },
        ];

        function defaults() {
            return {
                siteEnabled: true,
                districtAnnouncement: {
                    active: true,
                    text: 'Miami-Dade County Public Schools will be closed Monday, January 19 in observance of Dr. Martin Luther King Jr. Day.',
                },
                alert: {
                    active: false,
                    text: 'Weather update: After-school activities are canceled today.',
                },
                events: [
                    {
                        id: 'stem',
                        title: 'Family STEM Night',
                        date: '2026-03-12',
                        time: '6:00 PM - 8:00 PM',
                        location: 'Everglades Elementary Cafeteria',
                        description: 'Hands-on science stations, a planetarium dome, and a robotics showcase for the whole family.',
                    },
                    {
                        id: 'bookfair',
                        title: 'Spring Book Fair',
                        date: '2026-03-17',
                        time: '8:00 AM - 4:00 PM',
                        location: 'Media Center',
                        description: 'Browse hundreds of new titles and support our reading programs all week long.',
                    },
                    {
                        id: 'concert',
                        title: 'Spring Music Concert',
                        date: '2026-04-09',
                        time: '6:30 PM - 8:00 PM',
                        location: 'School Auditorium',
                        description: 'Our chorus and band perform their spring showcase. Doors open at 6:00 PM.',
                    },
                ],
                media: {
                    src: heroImage.src,
                    alt: heroImage.alt,
                    label: heroImage.label,
                },
                branding: {
                    accent: '#0b5cab',
                    accentName: 'Ocean Blue',
                    mascot: '🐊',
                    mascotName: 'Gator',
                    logo: null,
                },
            };
        }

        function hydrate() {
            const base = defaults();
            try {
                const raw = localStorage.getItem(STORAGE_KEY);
                if (!raw) {
                    return base;
                }
                const saved = JSON.parse(raw);

                // Migrate the legacy single `event` into the new `events` array.
                let events = base.events;
                if (Array.isArray(saved.events)) {
                    events = saved.events;
                } else if (saved.event && saved.event.title) {
                    events = [{ id: 'legacy', ...saved.event }];
                }

                // Shallow-merge each section so new default keys survive upgrades.
                return {
                    siteEnabled: typeof saved.siteEnabled === 'boolean' ? saved.siteEnabled : base.siteEnabled,
                    districtAnnouncement: { ...base.districtAnnouncement, ...(saved.districtAnnouncement || {}) },
                    alert: { ...base.alert, ...(saved.alert || {}) },
                    events: events,
                    media: { ...base.media, ...(saved.media || {}) },
                    branding: { ...base.branding, ...(saved.branding || {}) },
                };
            } catch (e) {
                return base;
            }
        }

        document.addEventListener('alpine:init', () => {
            const initial = hydrate();

            Alpine.store('mdcps', {
                siteEnabled: initial.siteEnabled,
                districtAnnouncement: initial.districtAnnouncement,
                alert: initial.alert,
                events: initial.events,
                media: initial.media,
                branding: initial.branding,
                sampleImages: sampleImages,
                accentPresets: accentPresets,
                mascotPresets: mascotPresets,

                // Date-sorted events; `upcoming` returns the soonest few.
                sortedEvents() {
                    return [...this.events].sort((a, b) => (a.date || '').localeCompare(b.date || ''));
                },

                upcoming(limit) {
                    return this.sortedEvents().slice(0, limit ?? 3);
                },

                addEvent(event) {
                    this.events.push({ id: 'evt-' + Date.now(), ...event });
                    this.persist();
                },

                updateEvent(event) {
                    const idx = this.events.findIndex((e) => e.id === event.id);
                    if (idx !== -1) {
                        this.events[idx] = { ...event };
                        this.persist();
                    }
                },

                deleteEvent(id) {
                    this.events = this.events.filter((e) => e.id !== id);
                    this.persist();
                },

                persist() {
                    const snapshot = {
                        siteEnabled: this.siteEnabled,
                        districtAnnouncement: this.districtAnnouncement,
                        alert: this.alert,
                        events: this.events,
                        media: this.media,
                        branding: this.branding,
                    };
                    localStorage.setItem(STORAGE_KEY, JSON.stringify(snapshot));
                },

                reset() {
                    localStorage.removeItem(STORAGE_KEY);
                    const d = defaults();
                    this.siteEnabled = d.siteEnabled;
                    this.districtAnnouncement = d.districtAnnouncement;
                    this.alert = d.alert;
                    this.events = d.events;
                    this.media = d.media;
                    this.branding = d.branding;
                },
            });
        });
    })();
</script>
