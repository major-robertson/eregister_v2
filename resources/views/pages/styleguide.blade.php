<x-layouts.portal :title="__('Style Guide')">
    <div class="mx-auto max-w-6xl px-6 py-8">
        <x-ui.page-header title="Style Guide" subtitle="Reusable UI components and design patterns">
            <x-slot:actions>
                <flux:button variant="primary" icon="bell" x-data
                    @click="$flux.toast({variant: 'success', text: 'This is a toast notification!'})">
                    Test Toast
                </flux:button>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- Colors --}}
        <section class="mb-12">
            <h2 class="mb-4 text-xl font-semibold text-text-primary">Colors</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg border border-border bg-white p-4">
                    <div class="mb-2 h-16 rounded-lg bg-primary"></div>
                    <div class="font-medium">Primary</div>
                    <div class="text-sm text-text-secondary">#377dff</div>
                </div>
                <div class="rounded-lg border border-border bg-white p-4">
                    <div class="mb-2 h-16 rounded-lg bg-action"></div>
                    <div class="font-medium">Action</div>
                    <div class="text-sm text-text-secondary">#4caf50</div>
                </div>
                <div class="rounded-lg border border-border bg-white p-4">
                    <div class="mb-2 h-16 rounded-lg bg-success"></div>
                    <div class="font-medium">Success</div>
                    <div class="text-sm text-text-secondary">#00c9a7</div>
                </div>
                <div class="rounded-lg border border-border bg-white p-4">
                    <div class="mb-2 h-16 rounded-lg bg-danger"></div>
                    <div class="font-medium">Danger</div>
                    <div class="text-sm text-text-secondary">#de4437</div>
                </div>
            </div>
        </section>

        {{-- Buttons --}}
        <section class="mb-12">
            <h2 class="mb-4 text-xl font-semibold text-text-primary">Buttons</h2>
            <x-ui.card>
                <div class="flex flex-wrap items-center gap-4">
                    <x-ui.action-button icon="plus-circle">Action (revenue only)</x-ui.action-button>
                    <flux:button variant="primary">Primary (Flux)</flux:button>
                    <flux:button variant="ghost">Ghost</flux:button>
                    <flux:button variant="danger">Danger</flux:button>
                </div>
                <div class="mt-4 rounded-lg border border-border bg-amber-500/5 p-4 text-sm text-text-primary">
                    <p class="font-semibold">Button policy</p>
                    <ul class="mt-2 list-disc space-y-1 ps-5 text-text-secondary">
                        <li><strong class="text-text-primary">Green action buttons are reserved for revenue moments only</strong> — start filing, start registration, checkout, pay. Green is the conversion color; keeping it scarce keeps it strong.</li>
                        <li>Every other in-portal button uses Flux primary (accent blue), ghost, or danger.</li>
                        <li>Urgency states (overdue deadlines) use <code>variant="danger"</code>, never green or amber.</li>
                        <li>Section accents (amber/indigo/emerald) are identity marks — chips, dots, icons — never buttons or surfaces.</li>
                    </ul>
                </div>
                <div class="mt-4 rounded-lg bg-zinc-50 p-4">
                    <code class="text-sm">&lt;x-ui.action-button href="..."&gt;Start filing&lt;/x-ui.action-button&gt; — or class="btn-action" for plain anchors</code>
                </div>
            </x-ui.card>
        </section>

        {{-- Stat Cards --}}
        <section class="mb-12">
            <h2 class="mb-4 text-xl font-semibold text-text-primary">Stat Cards</h2>
            <div class="grid gap-4 sm:grid-cols-3">
                <x-ui.stat-card value="12" label="Total Vendors" icon="users" color="primary" />
                <x-ui.stat-card value="48" label="Active Certificates" icon="document-text" color="success" />
                <x-ui.stat-card value="3" label="Expiring Soon" icon="exclamation-triangle" color="danger" />
            </div>
            <div class="mt-4 rounded-lg bg-zinc-50 p-4">
                <code
                    class="text-sm">&lt;x-ui.stat-card value="12" label="Total Vendors" icon="users" color="primary" /&gt;</code>
            </div>
        </section>

        {{-- Cards --}}
        <section class="mb-12">
            <h2 class="mb-4 text-xl font-semibold text-text-primary">Cards</h2>
            <div class="grid gap-4 lg:grid-cols-2">
                <x-ui.card>
                    <x-slot:header>
                        <h3 class="text-lg font-semibold">Card with Header</h3>
                    </x-slot:header>
                    <p class="text-text-secondary">This is a card component with a header slot. Use it for grouped
                        content sections.</p>
                </x-ui.card>
                <x-ui.card>
                    <p class="text-text-secondary">This is a simple card without a header. Great for basic content
                        blocks.</p>
                </x-ui.card>
            </div>
        </section>

        {{-- State Selection Cards --}}
        <section class="mb-12">
            <h2 class="mb-4 text-xl font-semibold text-text-primary">State Selection Cards</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <x-ui.state-card name="states[]" value="AL" label="Alabama" :selected="true" />
                <x-ui.state-card name="states[]" value="AK" label="Alaska" :selected="false" />
                <x-ui.state-card name="states[]" value="CA" label="California" :selected="false" :disabled="true"
                    disabled-reason="State tax registration required" />
                <x-ui.state-card name="states[]" value="CO" label="Colorado" :selected="true" />
            </div>
            <div class="mt-4 rounded-lg bg-zinc-50 p-4">
                <code
                    class="text-sm">&lt;x-ui.state-card name="states[]" value="AL" label="Alabama" :selected="true" /&gt;</code>
            </div>
        </section>

        {{-- Count Card --}}
        <section class="mb-12">
            <h2 class="mb-4 text-xl font-semibold text-text-primary">Count Card</h2>
            <div class="grid gap-4 lg:grid-cols-3">
                <div class="lg:col-span-2">
                    <x-ui.card>
                        <p class="text-text-secondary">The count card is designed to be used in a sidebar to show
                            selection summaries. It's sticky positioned and uses the action color.</p>
                    </x-ui.card>
                </div>
                <x-ui.count-card :count="3" label="States Selected" />
            </div>
        </section>

        {{-- Form Row --}}
        <section class="mb-12">
            <h2 class="mb-4 text-xl font-semibold text-text-primary">Horizontal Form Layout</h2>
            <x-ui.card>
                <x-slot:header>
                    <h3 class="text-lg font-semibold">Vendor Information</h3>
                </x-slot:header>
                <x-ui.form-row label="Legal Name" required>
                    <flux:input placeholder="ABC Suppliers Inc." />
                </x-ui.form-row>
                <x-ui.form-row label="Address Line 1" required>
                    <flux:input placeholder="123 Main Street" />
                </x-ui.form-row>
                <x-ui.form-row label="Address Line 2" optional>
                    <flux:input placeholder="Suite 100" />
                </x-ui.form-row>
                <x-ui.form-row label="State" required>
                    <flux:select>
                        <flux:select.option value="">Choose...</flux:select.option>
                        <flux:select.option value="NY">New York</flux:select.option>
                        <flux:select.option value="CA">California</flux:select.option>
                    </flux:select>
                </x-ui.form-row>
            </x-ui.card>
        </section>

        {{-- Info List --}}
        <section class="mb-12">
            <h2 class="mb-4 text-xl font-semibold text-text-primary">Info List</h2>
            <x-ui.card>
                <x-slot:header>
                    <h3 class="text-lg font-semibold">Business Information</h3>
                </x-slot:header>
                <x-ui.info-list :items="[
                    'Business' => 'Mike\'s Hardware LLC',
                    'DBA' => 'Mike\'s Hardware',
                    'Address' => 'Louisville, KY',
                    'Tax Registrations' => '1 states',
                ]" />
            </x-ui.card>
        </section>

        {{-- Tips --}}
        <section class="mb-12">
            <h2 class="mb-4 text-xl font-semibold text-text-primary">Tips Card</h2>
            <div class="grid gap-4 lg:grid-cols-3">
                <div class="lg:col-span-2">
                    <x-ui.card>
                        <p class="text-text-secondary">Tips cards are used in sidebars to provide helpful guidance to
                            users filling out forms.</p>
                    </x-ui.card>
                </div>
                <x-ui.tips>
                    <x-ui.tip>Enter the vendor's legal business name exactly as it appears on their business documents
                    </x-ui.tip>
                    <x-ui.tip>Having contact information helps when certificates need renewal</x-ui.tip>
                    <x-ui.tip>You can generate certificates for this vendor after creation</x-ui.tip>
                </x-ui.tips>
            </div>
        </section>

        {{-- Data Table --}}
        <section class="mb-12">
            <h2 class="mb-4 text-xl font-semibold text-text-primary">Data Table</h2>
            <x-ui.card :padding="false">
                <x-slot:header>
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">Recent Certificates</h3>
                        <flux:button variant="ghost" size="sm">View all</flux:button>
                    </div>
                </x-slot:header>
                <x-ui.data-table :headers="['Vendor', 'State', 'Status', 'Date']">
                    <tr class="hover:bg-zinc-50">
                        <td class="px-6 py-4">Valley Station Bolt Supply</td>
                        <td class="px-6 py-4">Alabama</td>
                        <td class="px-6 py-4">
                            <flux:badge color="green" size="sm">Active</flux:badge>
                        </td>
                        <td class="px-6 py-4 text-text-secondary">Dec 19</td>
                    </tr>
                    <tr class="hover:bg-zinc-50">
                        <td class="px-6 py-4">Valley Station Bolt Supply</td>
                        <td class="px-6 py-4">Connecticut</td>
                        <td class="px-6 py-4">
                            <flux:badge color="green" size="sm">Active</flux:badge>
                        </td>
                        <td class="px-6 py-4 text-text-secondary">Dec 19</td>
                    </tr>
                    <tr class="hover:bg-zinc-50">
                        <td class="px-6 py-4">Valley Station Bolt Supply</td>
                        <td class="px-6 py-4">Georgia</td>
                        <td class="px-6 py-4">
                            <flux:badge color="yellow" size="sm">Expiring</flux:badge>
                        </td>
                        <td class="px-6 py-4 text-text-secondary">Dec 19</td>
                    </tr>
                </x-ui.data-table>
            </x-ui.card>
        </section>

        {{-- Breadcrumb --}}
        <section class="mb-12">
            <h2 class="mb-4 text-xl font-semibold text-text-primary">Breadcrumb</h2>
            <x-ui.card>
                <x-ui.breadcrumb :items="[
                    ['label' => 'Certificates', 'url' => '#'],
                    ['label' => 'Generate', 'url' => '#'],
                ]" />
                <h3 class="text-xl font-bold text-text-primary">Generate Resale Certificate</h3>
                <div class="mt-4 rounded-lg bg-zinc-50 p-4">
                    <code
                        class="text-sm">&lt;x-ui.breadcrumb :items="[['label' => 'Certificates', 'url' => '#'], ['label' => 'Generate', 'url' => '#']]" /&gt;</code>
                </div>
            </x-ui.card>
        </section>

        {{-- Section Chips --}}
        <section class="mb-12">
            <h2 class="mb-4 text-xl font-semibold text-text-primary">Section Chips</h2>
            <x-ui.card>
                <p class="mb-4 text-text-secondary">Workspace identity marks. Rendered automatically by <code>x-ui.page-header</code> on workspace pages and in the mobile header. Identity colors never appear on buttons or surfaces.</p>
                <div class="flex flex-wrap items-center gap-3">
                    @foreach (app(\App\Support\Workspaces\WorkspaceRegistry::class)->all() as $workspace)
                        <x-ui.section-chip :workspace="$workspace" />
                    @endforeach
                </div>
                <div class="mt-4 rounded-lg bg-zinc-50 p-4">
                    <code class="text-sm">&lt;x-ui.section-chip :workspace="$workspace" /&gt;</code>
                </div>
            </x-ui.card>
        </section>

        {{-- Page Header --}}
        <section class="mb-12">
            <h2 class="mb-4 text-xl font-semibold text-text-primary">Page Header</h2>
            <x-ui.card>
                <x-ui.page-header title="Dashboard" subtitle="Welcome to your Resale Certificate Management System">
                    <x-slot:actions>
                        <flux:button variant="primary" icon="plus-circle" href="#">
                            Generate Certificate
                        </flux:button>
                    </x-slot:actions>
                </x-ui.page-header>
                <div class="rounded-lg bg-zinc-50 p-4">
                    <code
                        class="text-sm">&lt;x-ui.page-header title="..." subtitle="..."&gt;&lt;x-slot:actions&gt;...&lt;/x-slot:actions&gt;&lt;/x-ui.page-header&gt;</code>
                </div>
            </x-ui.card>
        </section>

        {{-- Toast Notifications --}}
        <section class="mb-12">
            <h2 class="mb-4 text-xl font-semibold text-text-primary">Toast Notifications</h2>
            <x-ui.card>
                <p class="mb-4 text-text-secondary">Toasts use Flux: <code>Flux::toast()</code> from PHP,
                    <code>$flux.toast()</code> from Alpine.</p>
                <div class="flex flex-wrap gap-3">
                    <button type="button"
                        class="rounded-lg bg-action px-4 py-2 text-sm font-medium text-white hover:bg-action-hover"
                        x-data @click="$flux.toast({variant: 'success', text: 'Alabama added to selection'})">
                        Success Toast
                    </button>
                    <button type="button"
                        class="rounded-lg bg-danger px-4 py-2 text-sm font-medium text-white hover:opacity-90" x-data
                        @click="$flux.toast({variant: 'danger', text: 'Failed to save changes'})">
                        Error Toast
                    </button>
                    <button type="button"
                        class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary-hover"
                        x-data @click="$flux.toast('Processing your request...')">
                        Info Toast
                    </button>
                </div>
                <div class="mt-4 rounded-lg bg-zinc-50 p-4">
                    <code
                        class="text-sm">Flux::toast(text: 'Alabama added to selection', variant: 'success');</code>
                </div>
            </x-ui.card>
        </section>

        {{-- Flux Pro Components --}}
        <section class="mb-12">
            <h2 class="mb-4 text-xl font-semibold text-text-primary">Flux Pro</h2>
            <x-ui.card>
                <div class="grid gap-6 md:grid-cols-2">
                    <flux:field>
                        <flux:label>Date picker</flux:label>
                        <flux:date-picker placeholder="Select a date" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Searchable select (combobox)</flux:label>
                        <flux:select variant="combobox" placeholder="Choose a state...">
                            <flux:select.option>Alabama</flux:select.option>
                            <flux:select.option>Alaska</flux:select.option>
                            <flux:select.option>Arizona</flux:select.option>
                            <flux:select.option>California</flux:select.option>
                            <flux:select.option>Colorado</flux:select.option>
                            <flux:select.option>Florida</flux:select.option>
                        </flux:select>
                    </flux:field>
                </div>
                <div class="mt-6">
                    <flux:accordion>
                        <flux:accordion.item heading="What does flux:accordion replace?">
                            Hand-rolled Alpine x-show toggles and native details elements.
                        </flux:accordion.item>
                        <flux:accordion.item heading="Where is it used?">
                            Filing wizard amount breakdown, project form additional dates, admin roles reference.
                        </flux:accordion.item>
                    </flux:accordion>
                </div>
                <div class="mt-6">
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>Component</flux:table.column>
                            <flux:table.column>Replaces</flux:table.column>
                            <flux:table.column>Status</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            <flux:table.row>
                                <flux:table.cell>flux:table</flux:table.cell>
                                <flux:table.cell>raw &lt;table&gt; + x-ui.data-table</flux:table.cell>
                                <flux:table.cell><flux:badge color="green" size="sm">Live</flux:badge></flux:table.cell>
                            </flux:table.row>
                            <flux:table.row>
                                <flux:table.cell>flux:date-picker</flux:table.cell>
                                <flux:table.cell>input type="date"</flux:table.cell>
                                <flux:table.cell><flux:badge color="green" size="sm">Live</flux:badge></flux:table.cell>
                            </flux:table.row>
                        </flux:table.rows>
                    </flux:table>
                </div>
            </x-ui.card>
        </section>
    </div>
</x-layouts.portal>