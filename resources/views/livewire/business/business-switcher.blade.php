<div class="w-full max-w-lg">
    @if ($businesses->isEmpty())
        {{-- No businesses - show create form (typeform style) --}}

        {{-- Progress dots: 4 dots if from liens (continuous flow), 2 dots otherwise --}}
        @php $isFromLiens = auth()->user()->signup_landing_path === '/liens'; @endphp
        <div class="mb-16 flex justify-center gap-2">
            <div class="h-2 w-2 rounded-full bg-primary"></div>
            <div class="h-2 w-2 rounded-full bg-zinc-300 dark:bg-zinc-600"></div>
            @if ($isFromLiens)
                <div class="h-2 w-2 rounded-full bg-zinc-300 dark:bg-zinc-600"></div>
                <div class="h-2 w-2 rounded-full bg-zinc-300 dark:bg-zinc-600"></div>
            @endif
        </div>

        <div class="text-center">
            <h1 class="text-3xl font-bold tracking-tight text-text-primary sm:text-4xl">
                What's your business name?
            </h1>
            <p class="mt-4 text-lg text-text-secondary">
                We'll use this to set up your account.
            </p>
        </div>

        <form wire:submit="createBusiness" class="mt-12 space-y-8">
            <div>
                <input
                    type="text"
                    wire:model="newBusinessName"
                    placeholder="Enter your business name"
                    autofocus
                    class="w-full border-0 border-b-2 border-border bg-transparent px-0 py-4 text-2xl font-medium text-text-primary placeholder:text-text-secondary/50 focus:border-primary focus:outline-none focus:ring-0"
                />
                @error('newBusinessName')
                    <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end">
                <flux:button type="submit" variant="primary" icon-trailing="arrow-right">
                    Continue
                </flux:button>
            </div>
        </form>

        @error('business')
            <div class="mt-6 rounded-lg bg-danger/10 p-4 text-center text-danger">
                {{ $message }}
            </div>
        @enderror
    @else
        {{-- Has businesses - show selection --}}
        <div class="text-center">
            <h1 class="text-4xl font-bold text-text-primary sm:text-5xl">
                Welcome back
            </h1>
            <p class="mt-4 text-lg text-text-secondary">
                Select a business to continue, or create a new one.
            </p>
        </div>

        <div class="mt-12 space-y-3">
            @foreach ($businesses as $business)
                <button
                    wire:key="business-{{ $business->id }}"
                    wire:click="selectBusiness({{ $business->id }})"
                    class="flex w-full items-center justify-between rounded-xl border-2 border-border bg-white p-5 text-left transition hover:border-primary hover:shadow-md"
                >
                    <div>
                        <div class="text-lg font-semibold text-text-primary">{{ $business->name ?? 'Unnamed Business' }}</div>
                        @if ($business->city && $business->state)
                            <div class="mt-1 text-sm text-text-secondary">{{ $business->city }}, {{ $business->state }}</div>
                        @endif
                    </div>
                    <div class="flex items-center gap-3">
                        @if (!$business->isOnboardingComplete())
                            <flux:badge color="yellow" size="sm">Setup Required</flux:badge>
                        @endif
                        <flux:icon name="chevron-right" class="size-5 text-text-secondary" />
                    </div>
                </button>
            @endforeach
        </div>

        <div class="mt-8 text-center">
            <p class="text-text-secondary">or</p>
        </div>

        <form wire:submit="createBusiness" class="mt-6">
            <div class="flex gap-3">
                <input
                    type="text"
                    wire:model="newBusinessName"
                    placeholder="Create new business..."
                    class="flex-1 rounded-xl border-2 border-border bg-white px-4 py-3 text-text-primary placeholder:text-text-secondary/50 focus:border-primary focus:outline-none focus:ring-0"
                />
                <flux:button type="submit" variant="primary">
                    Create
                </flux:button>
            </div>
            @error('newBusinessName')
                <p class="mt-2 text-sm text-danger">{{ $message }}</p>
            @enderror
        </form>

        @error('business')
            <div class="mt-6 rounded-lg bg-danger/10 p-4 text-center text-danger">
                {{ $message }}
            </div>
        @enderror
    @endif
</div>
