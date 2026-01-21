<div>
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-medium">Parties</h3>
        <flux:button wire:click="openModal" size="sm" icon="plus">
            Add Party
        </flux:button>
    </div>

    @if($parties->isEmpty())
        <p class="text-sm text-zinc-500">No parties added yet.</p>
        <div class="mt-2 flex gap-2">
            <flux:button wire:click="openModal" size="sm" variant="ghost">
                Add Party
            </flux:button>
            <flux:button wire:click="prefillClaimant(); openModal()" size="sm" variant="ghost">
                Add Yourself as Claimant
            </flux:button>
        </div>
    @else
        <div class="space-y-3">
            @foreach($parties as $party)
                <div class="flex items-start justify-between p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-sm">{{ $party->displayName() }}</span>
                            <flux:badge size="sm">{{ $party->role->label() }}</flux:badge>
                        </div>
                        @if($party->name !== $party->displayName())
                            <div class="text-xs text-zinc-500">{{ $party->name }}</div>
                        @endif
                        @if($party->addressLine())
                            <div class="text-xs text-zinc-500 mt-1">{{ $party->addressLine() }}</div>
                        @endif
                        @if($party->email || $party->phone)
                            <div class="text-xs text-zinc-500 mt-1">
                                {{ implode(' | ', array_filter([$party->email, $party->phone])) }}
                            </div>
                        @endif
                    </div>
                    <div class="flex gap-1">
                        <flux:button wire:click="openModal({{ $party->id }})" size="sm" variant="ghost" icon="pencil" />
                        <flux:button
                            wire:click="deleteParty({{ $party->id }})"
                            wire:confirm="Delete this party?"
                            size="sm"
                            variant="ghost"
                            icon="trash"
                        />
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Modal --}}
    <flux:modal wire:model="showModal" class="max-w-lg">
        <div class="space-y-4">
            <flux:heading>{{ $editingPartyId ? 'Edit Party' : 'Add Party' }}</flux:heading>

            <form wire:submit="save" class="space-y-4">
                <flux:field>
                    <flux:label>Role *</flux:label>
                    <flux:select wire:model="role">
                        @foreach($partyRoles as $roleOption)
                            <option value="{{ $roleOption->value }}">{{ $roleOption->label() }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="role" />
                </flux:field>

                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Name *</flux:label>
                        <flux:input wire:model="name" placeholder="John Smith" />
                        <flux:error name="name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Company Name</flux:label>
                        <flux:input wire:model="company_name" placeholder="ABC Construction" />
                        <flux:error name="company_name" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Address</flux:label>
                    <flux:input wire:model="address1" placeholder="Street address" />
                    <flux:error name="address1" />
                </flux:field>

                <flux:field>
                    <flux:input wire:model="address2" placeholder="Suite, unit, etc." />
                    <flux:error name="address2" />
                </flux:field>

                <div class="grid grid-cols-3 gap-4">
                    <flux:field>
                        <flux:label>City</flux:label>
                        <flux:input wire:model="city" />
                        <flux:error name="city" />
                    </flux:field>

                    <flux:field>
                        <flux:label>State</flux:label>
                        <flux:input wire:model="state" maxlength="2" placeholder="CA" />
                        <flux:error name="state" />
                    </flux:field>

                    <flux:field>
                        <flux:label>ZIP</flux:label>
                        <flux:input wire:model="zip" />
                        <flux:error name="zip" />
                    </flux:field>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Email</flux:label>
                        <flux:input type="email" wire:model="email" />
                        <flux:error name="email" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Phone</flux:label>
                        <flux:input wire:model="phone" />
                        <flux:error name="phone" />
                    </flux:field>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <flux:button type="button" wire:click="closeModal" variant="ghost">
                        Cancel
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ $editingPartyId ? 'Save Changes' : 'Add Party' }}
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
