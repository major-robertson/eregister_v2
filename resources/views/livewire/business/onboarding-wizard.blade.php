<div class="w-full max-w-lg">
    {{-- Minimal progress dots (step 2 of 2 - address is the final step) --}}
    <div class="mb-16 flex justify-center gap-2">
        <div class="h-2 w-2 rounded-full bg-primary"></div>
        <div class="h-2 w-2 rounded-full bg-primary"></div>
    </div>

    {{-- Business Address (typeform style) --}}
    <div class="text-center">
        <h1 class="text-3xl font-bold tracking-tight text-text-primary sm:text-4xl">
            Where is your business located?
        </h1>
        <p class="mt-4 text-lg text-text-secondary">
            Your primary business address.
        </p>
    </div>

    <form wire:submit="complete" class="mt-12 space-y-6">
        {{-- Street Address --}}
        <div>
            <label class="mb-2 block text-sm font-medium text-text-secondary">Street Address</label>
            <input
                type="text"
                wire:model="businessAddress.line1"
                placeholder="123 Main Street"
                autofocus
                class="w-full border-0 border-b-2 border-border bg-transparent px-0 py-3 text-xl font-medium text-text-primary placeholder:text-text-secondary/50 focus:border-primary focus:outline-none focus:ring-0"
            />
            @error('businessAddress.line1')
                <p class="mt-2 text-sm text-danger">{{ $message }}</p>
            @enderror
        </div>

        {{-- Address Line 2 --}}
        <div>
            <label class="mb-2 block text-sm font-medium text-text-secondary">Suite / Unit <span class="text-text-tertiary">(optional)</span></label>
            <input
                type="text"
                wire:model="businessAddress.line2"
                placeholder="Suite 100"
                class="w-full border-0 border-b-2 border-border bg-transparent px-0 py-3 text-xl font-medium text-text-primary placeholder:text-text-secondary/50 focus:border-primary focus:outline-none focus:ring-0"
            />
        </div>

        {{-- City, State, ZIP in a row --}}
        <div class="grid grid-cols-6 gap-6">
            <div class="col-span-3">
                <label class="mb-2 block text-sm font-medium text-text-secondary">City</label>
                <input
                    type="text"
                    wire:model="businessAddress.city"
                    placeholder="City"
                    class="w-full border-0 border-b-2 border-border bg-transparent px-0 py-3 text-xl font-medium text-text-primary placeholder:text-text-secondary/50 focus:border-primary focus:outline-none focus:ring-0"
                />
                @error('businessAddress.city')
                    <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="col-span-1">
                <label class="mb-2 block text-sm font-medium text-text-secondary">State</label>
                <select
                    wire:model="businessAddress.state"
                    class="w-full border-0 border-b-2 border-border bg-transparent px-0 py-3 text-xl font-medium text-text-primary focus:border-primary focus:outline-none focus:ring-0"
                >
                    <option value="">--</option>
                    @foreach ($states as $code => $name)
                        <option value="{{ $code }}">{{ $code }}</option>
                    @endforeach
                </select>
                @error('businessAddress.state')
                    <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="col-span-2">
                <label class="mb-2 block text-sm font-medium text-text-secondary">ZIP Code</label>
                <input
                    type="text"
                    wire:model="businessAddress.zip"
                    placeholder="12345"
                    class="w-full border-0 border-b-2 border-border bg-transparent px-0 py-3 text-xl font-medium text-text-primary placeholder:text-text-secondary/50 focus:border-primary focus:outline-none focus:ring-0"
                />
                @error('businessAddress.zip')
                    <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex justify-end pt-6">
            <flux:button type="submit" variant="primary" icon-trailing="check">
                Complete Setup
            </flux:button>
        </div>
    </form>
</div>
