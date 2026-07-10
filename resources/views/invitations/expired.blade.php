<x-layouts::auth title="Invitation Expired">
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('This invitation has expired')"
            :description="__('Ask a business owner or admin to send you a new invitation.')"
        />

        <flux:button :href="route('home')" variant="outline" class="w-full">
            {{ __('Back to eRegister') }}
        </flux:button>
    </div>
</x-layouts::auth>
