<div x-show="modalOpen"
     x-cloak
     @keydown.escape.window="modalOpen = false"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-graphite-950/85 backdrop-blur-sm"
     x-transition:enter="transition ease-out duration-150"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-100"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">

    <div class="absolute inset-0" @click="modalOpen = false"></div>

    <div x-show="modalOpen"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="relative z-10 max-w-5xl w-full bg-paper-100 dark:bg-graphite-900 rounded-sm shadow-2xl overflow-hidden border border-graphite-200 dark:border-graphite-700">

        <div class="px-5 py-3 border-b border-graphite-200 dark:border-graphite-800 flex items-center justify-between bg-graphite-50/60 dark:bg-graphite-950/40">
            <h3 class="font-mono text-xs uppercase tracking-[0.14em] text-graphite-600 dark:text-graphite-300" x-text="modalTitle"></h3>
            <button @click="modalOpen = false" class="text-graphite-400 hover:text-signal-600 dark:hover:text-signal-400 transition-colors rounded-sm p-1 hover:bg-graphite-200/60 dark:hover:bg-graphite-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <div class="p-4 sm:p-6 bg-graphite-100/40 dark:bg-graphite-950/40 flex items-center justify-center min-h-[300px]">
            <img :src="modalImg" :alt="modalTitle" class="max-h-[80vh] w-auto object-contain rounded-sm shadow-md">
        </div>
    </div>
</div>