<div 
    x-data="{ toasts: [] }"
    x-on:toast.window="toasts.push({...$event.detail, id: Date.now()}); setTimeout(() => toasts.shift(), 5000)"
    class="fixed bottom-4 right-4 z-50 flex flex-col gap-2"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div 
            x-show="true"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            :class="{
                'bg-action text-white': toast.type === 'success',
                'bg-danger text-white': toast.type === 'error',
                'bg-warning text-yellow-900': toast.type === 'warning',
                'bg-primary text-white': !['success', 'error', 'warning'].includes(toast.type),
            }"
            class="flex items-center gap-3 rounded-lg px-4 py-3 shadow-lg min-w-72"
        >
            <svg x-show="toast.type === 'success'" class="size-5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <svg x-show="toast.type === 'error'" class="size-5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <svg x-show="!['success', 'error'].includes(toast.type)" class="size-5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <div class="flex-1">
                <div class="font-medium" x-text="toast.title || (toast.type === 'success' ? 'Success' : toast.type === 'error' ? 'Error' : 'Notice')"></div>
                <div class="text-sm opacity-90" x-text="toast.message"></div>
            </div>
            <button @click="toasts = toasts.filter(t => t.id !== toast.id)" class="opacity-70 hover:opacity-100">
                <svg class="size-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>
    </template>
</div>
