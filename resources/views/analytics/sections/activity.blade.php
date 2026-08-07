<div x-show="activeTab === 'activity'" x-cloak class="space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @if($imgActive)
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 transition-colors">
            <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-3">Active Customers Over Time</h2>
            @include('analytics.partials.chart-panel', [
                'image' => $imgActive,
                'modal' => 'Active Customers Over Time',
                'alt' => 'Active Customers',
                'boxClass' => 'rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-700/50 p-2 cursor-pointer group',
                'imageClass' => 'w-full h-auto object-contain max-h-[400px] mx-auto rounded-lg group-hover:scale-[1.01] transition-transform duration-200',
            ])
        </div>
        @endif

        @if($imgChurn)
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 transition-colors">
            <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-3">Monthly Churn Percentage</h2>
            @include('analytics.partials.chart-panel', [
                'image' => $imgChurn,
                'modal' => 'Monthly Churn Percentage',
                'alt' => 'Churn Percentage',
                'boxClass' => 'rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-700/50 p-2 cursor-pointer group',
                'imageClass' => 'w-full h-auto object-contain max-h-[400px] mx-auto rounded-lg group-hover:scale-[1.01] transition-transform duration-200',
            ])
        </div>
        @endif
    </div>
</div>