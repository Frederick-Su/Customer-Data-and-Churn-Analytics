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
    <div class="bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 rounded-sm transition-colors">
        <div class="px-5 py-3 border-b border-graphite-200 dark:border-graphite-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="font-mono text-[11px] uppercase tracking-[0.14em] text-graphite-400 dark:text-graphite-500">Latest Renewals</h3>
                <p class="text-graphite-400 dark:text-graphite-500 text-xs mt-1">Calculated from (Price &minus; SellerFee) using latest customer renewals grouped by time.</p>
            </div>

            <div class="inline-flex p-1 bg-graphite-50 dark:bg-graphite-950/40 rounded-sm border border-graphite-200 dark:border-graphite-800 self-start sm:self-auto">
                <button @click="timeframe = 'all'"
                        :class="timeframe === 'all' ? 'bg-paper-100 dark:bg-graphite-800 text-signal-600 dark:text-signal-400 font-semibold' : 'text-graphite-500 hover:text-graphite-700 dark:hover:text-graphite-300'"
                        class="px-3 py-1.5 font-mono text-[11px] uppercase tracking-wider rounded-sm transition-all">
                    Yearly
                </button>
                <button @click="timeframe = 'monthly'"
                        :class="timeframe === 'monthly' ? 'bg-paper-100 dark:bg-graphite-800 text-signal-600 dark:text-signal-400 font-semibold' : 'text-graphite-500 hover:text-graphite-700 dark:hover:text-graphite-300'"
                        class="px-3 py-1.5 font-mono text-[11px] uppercase tracking-wider rounded-sm transition-all">
                    Monthly
                </button>
                <button @click="timeframe = 'weekly'"
                        :class="timeframe === 'weekly' ? 'bg-paper-100 dark:bg-graphite-800 text-signal-600 dark:text-signal-400 font-semibold' : 'text-graphite-500 hover:text-graphite-700 dark:hover:text-graphite-300'"
                        class="px-3 py-1.5 font-mono text-[11px] uppercase tracking-wider rounded-sm transition-all">
                    Weekly
                </button>
            </div>
        </div>

        <div class="p-5">
        <div class="overflow-hidden rounded-sm bg-graphite-50 dark:bg-graphite-950/40 border border-graphite-200 dark:border-graphite-800 p-2">
            @foreach($revenueCharts as $key => $chart)
                @if($chart['image'])
                <div x-show="timeframe === '{{ $key }}'" @if($key !== 'all') x-cloak @endif class="cursor-pointer group"
                     @click="openModal('{{ $chart['image']['url'] }}', '{{ $chart['modal'] }}')">
                    <img src="{{ $chart['image']['url'] }}" alt="{{ $chart['alt'] }}"
                         class="w-full h-auto object-contain max-h-[500px] mx-auto rounded-sm group-hover:opacity-90 transition-opacity">
                </div>
                @endif
            @endforeach
        </div>

        <div class="mt-6 border-t border-graphite-200 dark:border-graphite-800 pt-4">
            @foreach($revenueTables as $key => $table)
            <div x-show="timeframe === '{{ $key }}'" @if($key !== 'all') x-cloak @endif class="space-y-2">
                <h3 class="font-mono text-[11px] uppercase tracking-[0.14em] text-graphite-400 dark:text-graphite-500 mb-2">{{ $table['title'] }}</h3>

                @if(!empty($table['rows']))
                <div @class(['overflow-x-auto border border-graphite-200 dark:border-graphite-800 rounded-sm' => $key === 'all', 'max-h-[300px] overflow-y-auto border border-graphite-200 dark:border-graphite-800 rounded-sm' => $key !== 'all'])>
                    <table class="w-full text-xs text-left font-mono text-graphite-600 dark:text-graphite-300">
                        <thead class="bg-graphite-100 dark:bg-graphite-800/60 text-graphite-500 dark:text-graphite-400 uppercase tracking-wider @if($key !== 'all') sticky top-0 @endif">
                            <tr>
                                <th class="p-2.5">{{ $table['periodHeader'] }}</th>
                                <th class="p-2.5">Region</th>
                                <th class="p-2.5">{{ $table['countHeader'] }}</th>
                                <th class="p-2.5">Net Revenue</th>
                                <th class="p-2.5">{{ $table['avgHeader'] }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-graphite-200 dark:divide-graphite-800">
                            @foreach($table['rows'] as $row)
                            <tr>
                                <td class="p-2.5 font-medium text-graphite-800 dark:text-graphite-100">{{ $row[$table['periodKey']] ?? '-' }}</td>
                                <td class="p-2.5">{{ $row['Region'] ?? '-' }}</td>
                                <td class="p-2.5">{{ $row['Active_Customers'] ?? 0 }}</td>
                                <td class="p-2.5 font-medium text-good-600 dark:text-good-400">{{ number_format($row['Total_Revenue'] ?? 0, 2) }}</td>
                                <td class="p-2.5">{{ number_format($row['Average_Revenue'] ?? 0, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-xs text-graphite-400 italic">{{ $table['emptyText'] }}</p>
                @endif
            </div>
            @endforeach
        </div>
        </div>
    </div>
    @endif
</div>