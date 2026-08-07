<div x-show="activeTab === 'tenure'" x-cloak class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    @if($imgTenure)
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col justify-between transition-colors">
        <div>
            <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-3">Customer Tenure by Region</h2>
            @include('analytics.partials.chart-panel', [
                'image' => $imgTenure,
                'modal' => 'Customer Tenure by Region',
                'alt' => 'Tenure Plot',
                'boxClass' => 'rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-700/50 p-2 mb-4 cursor-pointer group',
                'imageClass' => 'w-full h-auto object-contain max-h-[350px] mx-auto rounded-lg group-hover:scale-[1.01] transition-transform duration-200',
            ])
        </div>

        @include('analytics.partials.pivot-summary-table', [
            'summary' => $summaries['2_tenure'] ?? null,
            'title' => 'Regional Metrics Summary',
        ])
    </div>
    @endif

    @if($imgLoyalty)
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col justify-between transition-colors">
        <div>
            <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-3">Average Customer Loyalty by Site</h2>
            @include('analytics.partials.chart-panel', [
                'image' => $imgLoyalty,
                'modal' => 'Average Customer Loyalty by Site',
                'alt' => 'Site Loyalty',
                'boxClass' => 'rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-700/50 p-2 mb-4 cursor-pointer group',
                'imageClass' => 'w-full h-auto object-contain max-h-[350px] mx-auto rounded-lg group-hover:scale-[1.01] transition-transform duration-200',
            ])
        </div>

        @include('analytics.partials.site-summary-table', [
            'summary' => $summaries['3_site_loyalty'] ?? null,
            'title' => 'Site Loyalty Breakdown',
        ])
    </div>
    @endif
</div>