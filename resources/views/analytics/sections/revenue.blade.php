<div x-show="activeTab === 'revenue'" x-cloak x-data="{ timeframe: 'all' }" class="space-y-6">
    @php
        $revenueCharts = [
            'all' => [
                'image' => $imgRevenueAll,
                'label' => 'Yearly Net Revenue',
                'modal' => 'Company Net Revenue (All Time)',
                'alt' => 'All Time Revenue',
            ],
            'monthly' => [
                'image' => $imgRevenueMonthly,
                'label' => 'Monthly Net Revenue',
                'modal' => 'Monthly Net Revenue',
                'alt' => 'Monthly Revenue',
            ],
            'weekly' => [
                'image' => $imgRevenueWeekly,
                'label' => 'Weekly Net Revenue',
                'modal' => 'Weekly Net Revenue',
                'alt' => 'Weekly Revenue',
            ],
        ];

        $yearlyRevenueRows = [];
        if (isset($summaries['7_active_revenue']['Total_Revenue'])) {
            foreach (array_keys($summaries['7_active_revenue']['Total_Revenue']) as $region) {
                $yearlyRevenueRows[] = [
                    'Region' => $region,
                    'Active_Customers' => $summaries['7_active_revenue']['Active_Customers'][$region] ?? 0,
                    'Total_Revenue' => $summaries['7_active_revenue']['Total_Revenue'][$region] ?? 0,
                    'Average_Revenue' => $summaries['7_active_revenue']['Average_Revenue'][$region] ?? 0,
                ];
            }
        }

        $revenueTables = [
            'all' => [
                'title' => 'Yearly Regional Summary',
                'rows' => $yearlyRevenueRows,
                'periodKey' => 'Region',
                'periodHeader' => 'Region',
                'countHeader' => 'Customer Renewals',
                'avgHeader' => 'Avg Revenue / Renewal',
                'emptyText' => 'Yearly summary data is not present in the payload.',
            ],
            'monthly' => [
                'title' => 'Monthly Revenue Breakdown by Region',
                'rows' => $summaries['7_active_revenue_monthly'] ?? [],
                'periodKey' => 'Month',
                'periodHeader' => 'Month',
                'countHeader' => 'Customer Renewals',
                'avgHeader' => 'Avg Revenue / Renewal',
                'emptyText' => 'Monthly summary data is not present in the payload.',
            ],
            'weekly' => [
                'title' => 'Weekly Revenue Breakdown by Region',
                'rows' => $summaries['7_active_revenue_weekly'] ?? [],
                'periodKey' => 'Week',
                'periodHeader' => 'Week',
                'countHeader' => 'Customer Renewals',
                'avgHeader' => 'Avg Revenue / Renewalks',
                'emptyText' => 'Weekly summary data is not present in the payload.',
            ],
        ];
    @endphp

    @if($imgRevenueAll || $imgRevenueMonthly || $imgRevenueWeekly)
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 transition-colors">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
            <div>
                <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100">Latest Renewals</h2>
                <p class="text-slate-500 dark:text-slate-400 text-xs mt-0.5">Calculated from (Price - SellerFee) using latest customer renewals grouped by time.</p>
            </div>

            <div class="inline-flex p-1 bg-slate-100 dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 self-start sm:self-auto">
                <button @click="timeframe = 'all'"
                        :class="timeframe === 'all' ? 'bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 shadow-sm font-semibold' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300'"
                        class="px-3 py-1.5 text-xs rounded-lg transition-all">
                    Yearly
                </button>
                <button @click="timeframe = 'monthly'"
                        :class="timeframe === 'monthly' ? 'bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 shadow-sm font-semibold' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300'"
                        class="px-3 py-1.5 text-xs rounded-lg transition-all">
                    Monthly
                </button>
                <button @click="timeframe = 'weekly'"
                        :class="timeframe === 'weekly' ? 'bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 shadow-sm font-semibold' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300'"
                        class="px-3 py-1.5 text-xs rounded-lg transition-all">
                    Weekly
                </button>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-700/50 p-2">
            @foreach($revenueCharts as $key => $chart)
                @if($chart['image'])
                <div x-show="timeframe === '{{ $key }}'" @if($key !== 'all') x-cloak @endif class="cursor-pointer group"
                     @click="openModal('{{ $chart['image']['url'] }}', '{{ $chart['modal'] }}')">
                    <img src="{{ $chart['image']['url'] }}" alt="{{ $chart['alt'] }}"
                         class="w-full h-auto object-contain max-h-[500px] mx-auto rounded-lg group-hover:scale-[1.01] transition-transform duration-200">
                </div>
                @endif
            @endforeach
        </div>

        <div class="mt-6 border-t border-slate-100 dark:border-slate-700/60 pt-4">
            @foreach($revenueTables as $key => $table)
            <div x-show="timeframe === '{{ $key }}'" @if($key !== 'all') x-cloak @endif class="space-y-2">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-400 mb-2">{{ $table['title'] }}</h3>

                @if(!empty($table['rows']))
                <div @class(['overflow-x-auto' => $key === 'all', 'max-h-[300px] overflow-y-auto' => $key !== 'all'])>
                    <table class="w-full text-xs text-left text-slate-600 dark:text-slate-300">
                        <thead class="bg-slate-100 dark:bg-slate-700/50 text-slate-700 dark:text-slate-200 font-medium @if($key !== 'all') sticky top-0 @endif">
                            <tr>
                                <th class="p-2 rounded-l">{{ $table['periodHeader'] }}</th>
                                <th class="p-2">Region</th>
                                <th class="p-2">{{ $table['countHeader'] }}</th>
                                <th class="p-2">Net Revenue</th>
                                <th class="p-2">{{ $table['avgHeader'] }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                            @foreach($table['rows'] as $row)
                            <tr>
                                <td class="p-2 font-medium text-slate-800 dark:text-slate-100">{{ $row[$table['periodKey']] ?? '-' }}</td>
                                <td class="p-2">{{ $row['Region'] ?? '-' }}</td>
                                <td class="p-2">{{ $row['Active_Customers'] ?? 0 }}</td>
                                <td class="p-2 font-medium text-emerald-600 dark:text-emerald-400">{{ number_format($row['Total_Revenue'] ?? 0, 2) }}</td>
                                <td class="p-2">{{ number_format($row['Average_Revenue'] ?? 0, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-xs text-slate-400 italic">{{ $table['emptyText'] }}</p>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>