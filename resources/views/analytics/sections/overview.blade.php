<div x-show="activeTab === 'overview'" x-cloak class="space-y-6">

    <div class="bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 rounded-sm transition-colors">
        <div class="px-5 py-3 border-b border-graphite-200 dark:border-graphite-800">
            <h3 class="font-mono text-[11px] uppercase tracking-[0.14em] text-graphite-400 dark:text-graphite-500">Mosaic Distribution Plot</h3>
        </div>
        <div class="p-4">
            @include('analytics.partials.chart-panel', [
                'image' => $img,
                'modal' => 'Mosaic Distribution Plot',
                'alt' => 'Mosaic Plot',
                'boxClass' => 'overflow-hidden rounded-sm bg-graphite-50 dark:bg-graphite-950/40 border border-graphite-200 dark:border-graphite-800 p-2 cursor-pointer group',
                'imageClass' => 'w-full h-auto object-contain max-h-[500px] mx-auto rounded-sm group-hover:opacity-90 transition-opacity',
            ])
            @include('analytics.partials.mosaic-summary-table', [
                'summary' => $summaries['1_mosaic'] ?? null,
                'title' => 'Customer Distribution Summary',
            ])
        </div>
    </div>
</div>