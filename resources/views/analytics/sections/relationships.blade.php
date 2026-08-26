<div x-show="activeTab === 'relationships'" x-cloak>

    @php
        $summary = $summaries['8_duration_vs_price'] ?? [];
    @endphp

    <div class="space-y-6">

        <!-- Graph -->
        @if($imgDurationPrice)

            <div class="bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 rounded-sm">

                <div class="px-5 py-3 border-b border-graphite-200 dark:border-graphite-800">
                    <h2 class="font-mono text-[11px] uppercase tracking-[0.14em] text-graphite-400 dark:text-graphite-500">
                        Contract Duration vs Price
                    </h2>

                    <p class="text-xs text-graphite-500 dark:text-graphite-400 mt-1">
                        Relationship between invoice-to-expiry duration and customer price.
                    </p>
                </div>

                <div class="p-5">
                    <img
                        src="{{ $imgDurationPrice['url'] }}"
                        alt="Contract Duration vs Price"
                        class="w-full rounded-sm cursor-pointer"
                        @click="openModal('{{ $imgDurationPrice['url'] }}', 'Contract Duration vs Price')"
                    >
                </div>

            </div>

        @endif


        <!-- Statistical Summary -->
        @if(!empty($summary))

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">

                <!-- Customers -->
                <div class="bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 border-l-2 border-l-graphite-400 dark:border-l-graphite-600 rounded-sm p-4">

                    <p class="font-mono text-[11px] uppercase tracking-wider text-graphite-400 dark:text-graphite-500">
                        Customers Analyzed
                    </p>

                    <h3 class="mt-2 font-mono text-2xl font-semibold text-graphite-900 dark:text-graphite-100">
                        {{ number_format($summary['Customers'] ?? 0) }}
                    </h3>

                    <p class="mt-2 text-xs text-graphite-500 dark:text-graphite-400">
                        Active customers included in the analysis.
                    </p>

                </div>


                <!-- Correlation -->
                <div class="bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 border-l-2 border-l-graphite-400 dark:border-l-graphite-600 rounded-sm p-4">

                    <p class="font-mono text-[11px] uppercase tracking-wider text-graphite-400 dark:text-graphite-500">
                        Pearson Correlation
                    </p>

                    <h3 class="mt-2 font-mono text-2xl font-semibold text-graphite-900 dark:text-graphite-100">
                        {{ number_format($summary['Correlation']['Pearson_R'] ?? 0, 4) }}
                    </h3>

                    <p class="mt-2 text-xs text-graphite-500 dark:text-graphite-400">
                        Strength of the relationship between duration and price.
                    </p>

                </div>


                <!-- R Squared -->
                <div class="bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 border-l-2 border-l-graphite-400 dark:border-l-graphite-600 rounded-sm p-4">

                    <p class="font-mono text-[11px] uppercase tracking-wider text-graphite-400 dark:text-graphite-500">
                        R&sup2;
                    </p>

                    <h3 class="mt-2 font-mono text-2xl font-semibold text-graphite-900 dark:text-graphite-100">
                        {{ number_format($summary['Correlation']['R_Squared'] ?? 0, 4) }}
                    </h3>

                    <p class="mt-2 text-xs text-graphite-500 dark:text-graphite-400">
                        Variation in price explained by duration.
                    </p>

                </div>


                <!-- Relationship -->
                <div class="bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 border-l-2 border-l-signal-600 dark:border-l-signal-500 rounded-sm p-4">

                    <p class="font-mono text-[11px] uppercase tracking-wider text-graphite-400 dark:text-graphite-500">
                        Relationship
                    </p>

                    <h3 class="mt-2 font-mono text-xl font-semibold text-signal-600 dark:text-signal-400">
                        {{ $summary['Interpretation']['Relationship'] ?? 'N/A' }}
                    </h3>

                    <p class="mt-2 text-xs text-graphite-500 dark:text-graphite-400">
                        Based on Pearson correlation.
                    </p>

                </div>

            </div>


            <!-- Detailed Statistics -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mt-6">

                <!-- Contract Duration -->
                <div class="bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 rounded-sm">

                    <div class="px-5 py-3 border-b border-graphite-200 dark:border-graphite-800">
                        <h3 class="font-mono text-[11px] uppercase tracking-[0.14em] text-graphite-400 dark:text-graphite-500">
                            Contract Duration
                        </h3>
                    </div>

                    <div class="p-5 space-y-3 font-mono text-sm">

                        <div class="flex justify-between">
                            <span class="text-graphite-500 dark:text-graphite-400">Average</span>
                            <span class="font-semibold text-graphite-900 dark:text-graphite-100">
                                {{ number_format($summary['Contract_Duration']['Average_Days'] ?? 0, 2) }} days
                            </span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-graphite-500 dark:text-graphite-400">Median</span>
                            <span class="font-semibold text-graphite-900 dark:text-graphite-100">
                                {{ number_format($summary['Contract_Duration']['Median_Days'] ?? 0, 2) }} days
                            </span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-graphite-500 dark:text-graphite-400">Minimum</span>
                            <span class="font-semibold text-graphite-900 dark:text-graphite-100">
                                {{ number_format($summary['Contract_Duration']['Minimum_Days'] ?? 0, 2) }} days
                            </span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-graphite-500 dark:text-graphite-400">Maximum</span>
                            <span class="font-semibold text-graphite-900 dark:text-graphite-100">
                                {{ number_format($summary['Contract_Duration']['Maximum_Days'] ?? 0, 2) }} days
                            </span>
                        </div>

                    </div>

                </div>


                <!-- Price -->
                <div class="bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 rounded-sm">

                    <div class="px-5 py-3 border-b border-graphite-200 dark:border-graphite-800">
                        <h3 class="font-mono text-[11px] uppercase tracking-[0.14em] text-graphite-400 dark:text-graphite-500">
                            Price
                        </h3>
                    </div>

                    <div class="p-5 space-y-3 font-mono text-sm">

                        <div class="flex justify-between">
                            <span class="text-graphite-500 dark:text-graphite-400">Average</span>
                            <span class="font-semibold text-graphite-900 dark:text-graphite-100">
                                {{ number_format($summary['Price']['Average'] ?? 0, 2) }}
                            </span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-graphite-500 dark:text-graphite-400">Median</span>
                            <span class="font-semibold text-graphite-900 dark:text-graphite-100">
                                {{ number_format($summary['Price']['Median'] ?? 0, 2) }}
                            </span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-graphite-500 dark:text-graphite-400">Minimum</span>
                            <span class="font-semibold text-graphite-900 dark:text-graphite-100">
                                {{ number_format($summary['Price']['Minimum'] ?? 0, 2) }}
                            </span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-graphite-500 dark:text-graphite-400">Maximum</span>
                            <span class="font-semibold text-graphite-900 dark:text-graphite-100">
                                {{ number_format($summary['Price']['Maximum'] ?? 0, 2) }}
                            </span>
                        </div>

                    </div>

                </div>

            </div>


            <!-- Statistical Significance -->
            <div class="bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 rounded-sm mt-6">

                <div class="px-5 py-3 border-b border-graphite-200 dark:border-graphite-800">
                    <h3 class="font-mono text-[11px] uppercase tracking-[0.14em] text-graphite-400 dark:text-graphite-500">
                        Statistical Analysis
                    </h3>
                </div>

                <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-5 font-mono">

                    <div>
                        <p class="text-xs text-graphite-500 dark:text-graphite-400">
                            P-value
                        </p>

                        <p class="mt-1 text-lg font-semibold text-graphite-900 dark:text-graphite-100">
                            {{ $summary['Correlation']['P_Value'] ?? 'N/A' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-graphite-500 dark:text-graphite-400">
                            Regression Slope
                        </p>

                        <p class="mt-1 text-lg font-semibold text-graphite-900 dark:text-graphite-100">
                            {{ number_format($summary['Regression']['Slope'] ?? 0, 4) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-graphite-500 dark:text-graphite-400">
                            Statistically Significant
                        </p>

                        <p class="mt-1 text-lg font-semibold
                            {{ ($summary['Interpretation']['Statistically_Significant'] ?? false)
                                ? 'text-good-600 dark:text-good-400'
                                : 'text-graphite-500 dark:text-graphite-400' }}">

                            {{ ($summary['Interpretation']['Statistically_Significant'] ?? false)
                                ? 'Yes'
                                : 'No' }}

                        </p>
                    </div>

                </div>

            </div>

        @endif

    </div>

</div>