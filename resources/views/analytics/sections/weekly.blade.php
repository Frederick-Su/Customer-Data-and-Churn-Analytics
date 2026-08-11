<div x-show="activeTab === 'weekly'" x-cloak class="space-y-6">
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 transition-colors">
        <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100 mb-1">Monthly Churn Rate by Site</h2>
        <p class="text-slate-500 dark:text-slate-400 text-xs mb-6">Historical trends across active sites over the last 12 months.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($weeklyChurnImages as $siteImg)
                @php $siteTitle = str_replace(['6_', '_weekly_churn'], '', $siteImg['name']) . ' Weekly Churn'; @endphp
                <div class="border border-slate-200 dark:border-slate-700 rounded-xl p-4 bg-slate-50/50 dark:bg-slate-900/30 hover:shadow-md transition-shadow">
                    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        {{ str_replace(['6_', '_weekly_churn'], '', $siteImg['name']) }}
                    </h3>

                    @include('analytics.partials.chart-panel', [
                        'image' => $siteImg,
                        'modal' => $siteTitle,
                        'alt' => $siteImg['name'],
                        'boxClass' => 'bg-white dark:bg-slate-800 rounded-lg p-2 border border-slate-100 dark:border-slate-700/50 cursor-pointer group',
                        'imageClass' => 'w-full h-auto object-contain max-h-[280px] rounded group-hover:scale-[1.01] transition-transform duration-200',
                    ])
                </div>
            @endforeach
        </div>
    </div>
</div>