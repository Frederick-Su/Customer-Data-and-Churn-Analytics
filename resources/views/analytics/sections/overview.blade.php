<div x-show="activeTab === 'overview'" x-cloak class="space-y-6">

    @include('analytics.partials.chart-panel', [
        'image' => $img,
        'modal' => 'Mosaic Distribution Plot',
        'alt' => 'Mosaic Plot',
        'boxClass' => 'bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 transition-colors overflow-hidden rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-700/50 p-2 cursor-pointer group',
        'imageClass' => 'w-full h-auto object-contain max-h-[500px] mx-auto rounded-lg group-hover:scale-[1.01] transition-transform duration-200',
    ])
    @include('analytics.partials.mosaic-summary-table', [
        'summary' => $summaries['1_mosaic'] ?? null,
        'title' => 'Customer Distribution Summary',
    ])
</div>